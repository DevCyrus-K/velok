import B2 from 'backblaze-b2';
import crypto from 'node:crypto';
import fs from 'node:fs/promises';
import path from 'node:path';
import multer from 'multer';

const requiredEnv = [
  'B2_APPLICATION_KEY_ID',
  'B2_APPLICATION_KEY',
  'B2_BUCKET_ID',
  'B2_BUCKET_NAME',
];

const missingEnv = requiredEnv.filter((key) => !process.env[key]);

if (missingEnv.length > 0) {
  throw new Error(`Backblaze B2 PDF storage is not configured. Missing: ${missingEnv.join(', ')}`);
}

export const b2 = new B2({
  applicationKeyId: process.env.B2_APPLICATION_KEY_ID,
  applicationKey: process.env.B2_APPLICATION_KEY,
});

let b2Authorized = false;

export async function authorizeB2() {
  if (!b2Authorized) {
    await b2.authorize();
    b2Authorized = true;
  }
}

async function withB2Retry(operation) {
  try {
    await authorizeB2();
    return await operation();
  } catch (error) {
    if (error?.response?.status !== 401 && error?.status !== 401) {
      throw error;
    }

    b2Authorized = false;
    await authorizeB2();

    return operation();
  }
}

function cleanFolder(folder = 'pdfs') {
  return String(folder).replace(/[^A-Za-z0-9_/-]/g, '').replace(/^\/+|\/+$/g, '') || 'pdfs';
}

function sanitizeFilename(filename = 'document.pdf') {
  const parsed = path.parse(filename);
  const base = (parsed.name || 'document')
    .normalize('NFKD')
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/\s+/g, '_')
    .slice(0, 120) || 'document';

  return `${base}_${Math.floor(Date.now() / 1000)}_${crypto.randomUUID().slice(0, 6)}.pdf`;
}

export async function uploadPDF(buffer, filename, folder = 'pdfs') {
  const safeFolder = cleanFolder(folder);
  const sanitizedFilename = sanitizeFilename(filename);
  const fullFileName = `${safeFolder}/${sanitizedFilename}`;

  try {
    return await withB2Retry(async () => {
      const { data: uploadUrlData } = await b2.getUploadUrl({
        bucketId: process.env.B2_BUCKET_ID,
      });

      const { data } = await b2.uploadFile({
        uploadUrl: uploadUrlData.uploadUrl,
        uploadAuthToken: uploadUrlData.authorizationToken,
        fileName: fullFileName,
        data: buffer,
        mime: 'application/pdf',
        onUploadProgress: null,
      });

      return {
        key: data.fileName,
        fileId: data.fileId,
        url: `${(process.env.B2_BUCKET_BASE_URL || '').replace(/\/+$/, '')}/${data.fileName}`,
        filename: sanitizedFilename,
        bucket: process.env.B2_BUCKET_NAME,
      };
    });
  } catch (error) {
    console.error('Backblaze B2 PDF upload failed', {
      filename,
      folder: safeFolder,
      error,
    });
    throw new Error(`Could not upload ${filename} to Backblaze B2.`);
  }
}

export async function deletePDF(fileId, fileName) {
  try {
    await withB2Retry(() => b2.deleteFileVersion({ fileId, fileName }));

    return { deleted: true, fileName };
  } catch (error) {
    console.error('Backblaze B2 PDF delete failed', { fileId, fileName, error });
    throw new Error(`Could not delete ${fileName} from Backblaze B2.`);
  }
}

export async function getPDFDownloadUrl(fileName) {
  return withB2Retry(async () => {
    const { data: authData } = await b2.getDownloadAuthorization({
      bucketId: process.env.B2_BUCKET_ID,
      fileNamePrefix: fileName,
      validDurationInSeconds: 3600,
    });

    return `${authData.downloadUrl}/file/${process.env.B2_BUCKET_NAME}/${fileName}?Authorization=${authData.authorizationToken}`;
  });
}

export async function uploadPDFFromLocalPath(localFilePath, folder = 'pdfs') {
  try {
    const buffer = await fs.readFile(localFilePath);

    return await uploadPDF(buffer, path.basename(localFilePath), folder);
  } catch (error) {
    await fs.unlink(localFilePath).catch(() => {});
    throw error;
  } finally {
    await fs.unlink(localFilePath).catch(() => {});
  }
}

export const pdfUpload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 50 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    if (file.mimetype === 'application/pdf') {
      cb(null, true);
    } else {
      cb(new Error('Only PDF files are allowed'), false);
    }
  },
});

export function createPDFUploadMiddleware(folder = 'pdfs', fieldName = 'file') {
  const multerMiddleware = pdfUpload.single(fieldName);

  return (req, res, next) => {
    multerMiddleware(req, res, async (error) => {
      if (error) {
        next(error);
        return;
      }

      if (!req.file) {
        next();
        return;
      }

      try {
        const uploaded = await uploadPDF(req.file.buffer, req.file.originalname, folder);
        req.b2File = {
          key: uploaded.key,
          fileId: uploaded.fileId,
          url: uploaded.url,
        };
        next();
      } catch (uploadError) {
        next(uploadError);
      }
    });
  };
}

export async function testB2Connection() {
  try {
    await withB2Retry(() => b2.listFileNames({
      bucketId: process.env.B2_BUCKET_ID,
      maxFileCount: 1,
    }));

    return { connected: true, provider: 'backblaze-b2' };
  } catch (error) {
    return { connected: false, provider: 'backblaze-b2', error: error.message };
  }
}
