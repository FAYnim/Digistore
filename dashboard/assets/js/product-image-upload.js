let productImageUploading = false;
let productImageOldPath = '';

function productImageShowPlaceholder() {
  document.querySelector('#productImagePlaceholder')?.classList.remove('hidden');
  document.querySelector('#productImagePreview')?.classList.add('hidden');
}

function productImageShowPreview(src, fileName, fileSize) {
  const img = document.querySelector('#productImagePreviewImg');
  const nameEl = document.querySelector('#productImageFileName');
  const sizeEl = document.querySelector('#productImageFileSize');
  if (img) img.src = src;
  if (nameEl) nameEl.textContent = fileName || '';
  if (sizeEl) sizeEl.textContent = fileSize ? (fileSize / 1024).toFixed(1) + ' KB' : '';
  document.querySelector('#productImagePlaceholder')?.classList.add('hidden');
  document.querySelector('#productImagePreview')?.classList.remove('hidden');
}

function productImageSetProgress(percent) {
  const bar = document.querySelector('#productImageProgressBar');
  const wrap = document.querySelector('#productImageUploadProgress');
  if (bar) bar.style.width = percent + '%';
  if (wrap) wrap.classList.toggle('hidden', percent <= 0 || percent >= 100);
}

function productImageValidateFile(file) {
  const allowed = ['image/jpeg', 'image/png'];
  if (!allowed.includes(file.type)) {
    showToast('Format gambar hanya JPG, JPEG, atau PNG.', 'error');
    return false;
  }
  if (file.size > 2 * 1024 * 1024) {
    showToast('Ukuran gambar maksimal 2MB.', 'error');
    return false;
  }
  return true;
}

async function productImageResize(file) {
  return new Promise((resolve) => {
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      URL.revokeObjectURL(url);
      const maxSize = 800;
      let w = img.width;
      let h = img.height;
      if (w > maxSize || h > maxSize) {
        if (w > h) { h = Math.round(h * maxSize / w); w = maxSize; }
        else { w = Math.round(w * maxSize / h); h = maxSize; }
      }
      const canvas = document.createElement('canvas');
      canvas.width = w;
      canvas.height = h;
      canvas.getContext('2d').drawImage(img, 0, 0, w, h);
      canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.85);
    };
    img.onerror = () => resolve(file);
    img.src = url;
  });
}

async function productImageUploadFile(file) {
  if (productImageUploading) return;
  productImageUploading = true;

  const reader = new FileReader();
  reader.onload = () => productImageShowPreview(reader.result, file.name, file.size);
  reader.readAsDataURL(file);

  productImageSetProgress(10);

  const resized = await productImageResize(file);
  productImageSetProgress(30);

  const form = new FormData();
  form.append('image', resized, file.name);
  if (productImageOldPath) form.append('old_image', productImageOldPath);

  try {
    productImageSetProgress(50);
    const res = await api.upload('api/upload-product-image', form);
    productImageSetProgress(100);

    if (res.success && res.data?.path) {
      document.querySelector('#productImage').value = res.data.path;
      productImageOldPath = res.data.path;
      showToast('Gambar berhasil diupload.');
    } else {
      showToast(res.message || 'Upload gagal.', 'error');
      productImageRemoveImage();
    }
  } catch {
    showToast('Upload gagal.', 'error');
    productImageRemoveImage();
  } finally {
    productImageUploading = false;
    setTimeout(() => productImageSetProgress(0), 500);
  }
}

function productImageRemoveImage() {
  document.querySelector('#productImage').value = '';
  document.querySelector('#productImageFileInput').value = '';
  productImageOldPath = '';
  productImageShowPlaceholder();
}

function initProductImageDropzone() {
  const dropArea = document.querySelector('#productImageDropArea');
  const fileInput = document.querySelector('#productImageFileInput');
  const removeBtn = document.querySelector('#productImageRemoveBtn');

  if (!dropArea || !fileInput) return;

  ['dragenter', 'dragover'].forEach((evt) => {
    dropArea.addEventListener(evt, (e) => {
      e.preventDefault();
      dropArea.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-950');
    });
  });

  ['dragleave', 'drop'].forEach((evt) => {
    dropArea.addEventListener(evt, (e) => {
      e.preventDefault();
      dropArea.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-950');
    });
  });

  dropArea.addEventListener('drop', (e) => {
    const file = e.dataTransfer?.files?.[0];
    if (file && productImageValidateFile(file)) productImageUploadFile(file);
  });

  fileInput.addEventListener('change', () => {
    const file = fileInput.files?.[0];
    if (file && productImageValidateFile(file)) productImageUploadFile(file);
    else fileInput.value = '';
  });

  removeBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    productImageRemoveImage();
  });
}

function productImageSetFromUrl(url) {
  productImageOldPath = url || '';
  if (url && url.startsWith('uploads/products/')) {
    productImageShowPreview('../' + url, url.split('/').pop(), 0);
  } else if (url) {
    productImageShowPreview(url, url.split('/').pop(), 0);
  } else {
    productImageShowPlaceholder();
  }
}
