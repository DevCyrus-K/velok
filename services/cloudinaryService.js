import { v2 as cloudinary } from 'cloudinary';
import multer from 'multer';
import { CloudinaryStorage } from 'multer-storage-cloudinary';
import { Readable } from 'node:stream';

const requiredEnv = [
  'CLOUDINARY_CLOUD_NAME',
  'CLOUDINARY_API_KEY',
  'CLOUDINARY_API_SECRET',
];

const missingEnv = requiredEnv.filter((key) => !process.env[key]);

if (missingEnv.length > 0) {
  throw new Error(`Cloudinary image storage is not configured. Missing: ${missingEnv.join(', ')}`);
}

cloudinary.config({
  cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
  api_key: process.env.CLOUDINARY_API_KEY,
  api_secret: process.env.CLOUDINARY_API_SECRET,
  secure: true,
});

const imageMimeTypes = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

function imageFilter(req, file, cb) {
  if (!imageMimeTypes.has(file.mimetype)) {
    cb(new Error('Only image files are allowed'), false);
    return;
  }

  cb(null, true);
}

function storage(folder, formats, transformation = undefined) {
  return new CloudinaryStorage({
    cloudinary,
    params: async (req, file) => ({
      folder,
      resource_type: 'image',
      allowed_formats: formats,
      transformation,
      public_id: `${Date.now()}_${file.originalname
        .replace(/\.[^.]+$/, '')
        .normalize('NFKD')
        .replace(/[^\w\s-]/g, '')
        .trim()
        .replace(/\s+/g, '_')
        .slice(0, 100) || 'image'}`,
    }),
  });
}

export const avatarUpload = multer({
  storage: storage('avatars', ['jpg', 'jpeg', 'png', 'webp'], [
    { width: 500, height: 500, crop: 'limit', quality: 'auto', fetch_format: 'auto' },
  ]),
  limits: { fileSize: 5 * 1024 * 1024 },
  fileFilter: imageFilter,
});

export const jobImageUpload = multer({
  storage: storage('jobs', ['jpg', 'jpeg', 'png', 'webp'], [
    { width: 1200, height: 1200, crop: 'limit', quality: 'auto', fetch_format: 'auto' },
  ]),
  limits: { fileSize: 10 * 1024 * 1024 },
  fileFilter: imageFilter,
});

export const generalImageUpload = multer({
  storage: storage('general', ['jpg', 'jpeg', 'png', 'webp', 'gif']),
  limits: { fileSize: 10 * 1024 * 1024 },
  fileFilter: imageFilter,
});

export async function deleteImage(publicId) {
  try {
    await cloudinary.uploader.destroy(publicId);

    return { deleted: true, publicId };
  } catch (error) {
    console.error('Cloudinary image delete failed', { publicId, error });
    throw new Error(`Could not delete image ${publicId} from Cloudinary.`);
  }
}

export async function uploadImageFromBuffer(buffer, folder = 'general', filename = 'image') {
  try {
    return await new Promise((resolve, reject) => {
      const stream = cloudinary.uploader.upload_stream(
        {
          folder,
          resource_type: 'image',
          public_id: filename
            .replace(/\.[^.]+$/, '')
            .normalize('NFKD')
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '_')
            .slice(0, 100) || 'image',
        },
        (error, result) => {
          if (error || !result) {
            reject(error || new Error('Cloudinary did not return an upload result.'));
            return;
          }

          resolve({ url: result.secure_url, publicId: result.public_id });
        },
      );

      Readable.from(buffer).pipe(stream);
    });
  } catch (error) {
    console.error('Cloudinary image buffer upload failed', { folder, filename, error });
    throw new Error(`Could not upload image ${filename} to Cloudinary.`);
  }
}

export async function testCloudinaryConnection() {
  try {
    await cloudinary.api.ping();

    return { connected: true, provider: 'cloudinary' };
  } catch (error) {
    return { connected: false, provider: 'cloudinary', error: error.message };
  }
}

export { cloudinary };
