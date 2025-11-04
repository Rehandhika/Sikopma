import * as FilePond from 'filepond';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';

FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImageTransform
);

FilePond.setOptions({
    labelIdle: 'Seret & Letakkan file atau <span class="filepond--label-action">Pilih File</span>',
    labelInvalidField: 'Field berisi file yang tidak valid',
    labelFileWaitingForSize: 'Menunggu ukuran',
    labelFileSizeNotAvailable: 'Ukuran tidak tersedia',
    labelFileLoading: 'Memuat',
    labelFileLoadError: 'Error saat memuat',
    labelFileProcessing: 'Mengupload',
    labelFileProcessingComplete: 'Upload selesai',
    labelFileProcessingAborted: 'Upload dibatalkan',
    labelFileProcessingError: 'Error saat upload',
    labelFileProcessingRevertError: 'Error saat revert',
    labelFileRemoveError: 'Error saat menghapus',
    labelTapToCancel: 'tap untuk membatalkan',
    labelTapToRetry: 'tap untuk mengulang',
    labelTapToUndo: 'tap untuk undo',
    labelButtonRemoveItem: 'Hapus',
    labelButtonAbortItemLoad: 'Batal',
    labelButtonRetryItemLoad: 'Ulangi',
    labelButtonAbortItemProcessing: 'Batal',
    labelButtonUndoItemProcessing: 'Undo',
    labelButtonRetryItemProcessing: 'Ulangi',
    labelButtonProcessItem: 'Upload',
    labelMaxFileSizeExceeded: 'File terlalu besar',
    labelMaxFileSize: 'Maksimal ukuran file adalah {filesize}',
    labelMaxTotalFileSizeExceeded: 'Maksimal total ukuran terlampaui',
    labelMaxTotalFileSize: 'Maksimal total ukuran file adalah {filesize}',
    labelFileTypeNotAllowed: 'Tipe file tidak valid',
    fileValidateTypeLabelExpectedTypes: 'Tipe file yang diperbolehkan {allButLastType} atau {lastType}',
});

window.initFilePondImage = function(selector, options = {}) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return null;

    return FilePond.create(element, {
        acceptedFileTypes: ['image/png', 'image/jpeg', 'image/jpg'],
        maxFileSize: '5MB',
        imagePreviewHeight: 170,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 800,
        imageResizeTargetHeight: 800,
        imageTransformOutputQuality: 80,
        stylePanelLayout: 'compact circle',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        ...options
    });
};

window.initFilePondDocument = function(selector, options = {}) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return null;

    return FilePond.create(element, {
        maxFileSize: '10MB',
        ...options
    });
};
// Livewire v3 friendly integration without Blade directives
window.initFilePondLivewire = function(selector, wireModel, options = {}) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return null;

    // Try to resolve Livewire component instance from closest root (walk up DOM)
    let livewireInstance = null;
    try {
        let node = element;
        while (node && node !== document) {
            if (node.getAttribute && node.hasAttribute('wire:id')) {
                const id = node.getAttribute('wire:id');
                if (window.Livewire && typeof window.Livewire.find === 'function') {
                    livewireInstance = window.Livewire.find(id);
                }
                break;
            }
        }
    } catch (_) {}

    const pond = FilePond.create(element, {
        server: livewireInstance ? {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                // progress is a function (isComputable, loaded, total)
                livewireInstance.upload(wireModel, file, load, error, progress);
            },
            revert: (uniqueFileId, load, error) => {
                livewireInstance.removeUpload(wireModel, uniqueFileId, load);
            }
        } : undefined,
        ...options
    });

    return pond;
};
// Auto-init
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-filepond-image]').forEach(el => { initFilePondImage(el); });
    document.querySelectorAll('[data-filepond-document]').forEach(el => { initFilePondDocument(el); });
});

export { FilePond };
