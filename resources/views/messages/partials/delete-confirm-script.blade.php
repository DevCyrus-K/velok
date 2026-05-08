        function deleteAttribute(source, name) {
            return source?.getAttribute(`data-delete-${name}`) || '';
        }

        function confirmMessageDelete(source) {
            const modalElement = document.getElementById('deleteConfirmModal');
            const titleElement = document.getElementById('deleteConfirmModalTitle');
            const messageElement = document.getElementById('deleteConfirmModalMessage');
            const confirmButton = document.getElementById('deleteConfirmButton');
            const cancelButton = document.getElementById('deleteConfirmCancelButton');
            const fallbackMessage = deleteAttribute(source, 'message') || 'Delete this message?';

            if (!modalElement || !confirmButton || typeof bootstrap === 'undefined') {
                return Promise.resolve(window.confirm(fallbackMessage));
            }

            return new Promise((resolve) => {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                let settled = false;

                if (titleElement) {
                    titleElement.textContent = deleteAttribute(source, 'title') || 'Delete message?';
                }

                if (messageElement) {
                    messageElement.textContent = fallbackMessage;
                }

                confirmButton.textContent = deleteAttribute(source, 'confirm-text') || 'Delete it';
                confirmButton.className = 'btn btn-danger';

                if (cancelButton) {
                    cancelButton.textContent = deleteAttribute(source, 'cancel-text') || 'Keep it';
                }

                const settle = (confirmed) => {
                    if (settled) {
                        return;
                    }

                    settled = true;
                    confirmButton.removeEventListener('click', onConfirm);
                    modalElement.removeEventListener('hidden.bs.modal', onHidden);
                    resolve(confirmed);
                };

                const onConfirm = () => {
                    modal.hide();
                    settle(true);
                };

                const onHidden = () => settle(false);

                confirmButton.addEventListener('click', onConfirm);
                modalElement.addEventListener('hidden.bs.modal', onHidden);
                modal.show();
            });
        }
