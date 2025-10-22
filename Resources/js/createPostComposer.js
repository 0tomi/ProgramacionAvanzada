(function (global) {
  const DEFAULT_SELECTOR = '.js-create-post-form';
  const DATA_KEY = 'createPostReady';

  function init(options = {}) {
    const selector = options.selector || DEFAULT_SELECTOR;
    const forms = Array.from(document.querySelectorAll(selector));
    forms.forEach((form) => setupForm(form, options));
    return forms;
  }

  function setupForm(form, options = {}) {
    if (!form || form.dataset[DATA_KEY] === '1') return;
    form.dataset[DATA_KEY] = '1';

    const preview = form.querySelector('.compose__previews');
    const { input: fileInput, trigger: fileTrigger } = getFileControl(form);

    if (fileInput && preview) {
      fileInput.addEventListener('change', () => handleFileChange(form, preview, fileInput, options));
    }

    if (fileTrigger) {
      fileTrigger.addEventListener('click', (event) => guardFileTrigger(form, event));
      fileTrigger.addEventListener('keydown', (event) => guardFileTrigger(form, event));
    }

    form.addEventListener('reset', () => {
      clearPreview(form);
    });

    if (typeof options.onSubmit === 'function') {
      form.addEventListener('submit', options.onSubmit);
    }
  }

  function handleFileChange(form, preview, input, options) {
    clearPreview(form);

    const files = Array.from(input.files || []).filter(
      (file) => file && file.type && file.type.startsWith('image/')
    );

    const allowMultiple = input && input.hasAttribute('multiple');
    let images = files;

    if (!allowMultiple) {
      images = files.slice(0, 1);
      if (files.length > images.length) {
        reportError('Solo podés agregar una imagen por post.', options);
      }
    }

    if (images.length === 0) {
      if ((input.files || []).length > 0) {
        reportError('El archivo seleccionado no es una imagen compatible.', options);
        input.value = '';
      }
      preview.hidden = true;
      unlockFileInput(form);
      return;
    }

    const fragment = document.createDocumentFragment();
    images.forEach((file) => {
      const url = URL.createObjectURL(file);
      const item = document.createElement('div');
      item.className = 'compose__preview-item';
      item.dataset.previewUrl = url;

      const img = document.createElement('img');
      img.src = url;
      img.alt = file.name ? `Vista previa de ${file.name}` : 'Vista previa de la imagen seleccionada';
      item.appendChild(img);

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'compose__preview-remove';
      removeBtn.setAttribute('aria-label', 'Quitar imagen');
      removeBtn.textContent = '×';
      item.appendChild(removeBtn);

      fragment.appendChild(item);
    });

    preview.appendChild(fragment);
    preview.hidden = false;

    if (!allowMultiple) {
      lockFileInput(form);
    }
  }

  function clearPreview(form) {
    const preview = form.querySelector('.compose__previews');
    if (!preview) return;

    preview.querySelectorAll('.compose__preview-item').forEach((item) => {
      const url = item.dataset.previewUrl;
      if (url) {
        URL.revokeObjectURL(url);
      }
    });

    preview.innerHTML = '';
    preview.hidden = true;
    unlockFileInput(form);
  }

  function clearSelection(form) {
    const input = form.querySelector('input[type="file"]');
    if (input) {
      input.value = '';
    }
    clearPreview(form);
  }

  function reportError(message, options = {}) {
    if (typeof options.onError === 'function') {
      options.onError(message);
    } else if (global.FancyAlerts && typeof global.FancyAlerts.show === 'function') {
      global.FancyAlerts.show({ type: 'error', icon: '!', msg: message, timeout: 4000, closable: true });
    } else {
      alert(message);
    }
  }

  document.addEventListener('click', (event) => {
    const btn = event.target.closest('.compose__preview-remove');
    if (!btn) return;
    const form = btn.closest('form');
    if (!form) return;

    event.preventDefault();
    event.stopPropagation();
    clearSelection(form);
  });

  function getFileControl(form) {
    if (!form) return { input: null, trigger: null };
    const input = form.querySelector('input[type="file"]');
    const trigger = form.querySelector('.composer__pick');
    return { input, trigger };
  }

  function lockFileInput(form) {
    if (!form) return;
    form.dataset.composerImageLocked = '1';
    const { trigger } = getFileControl(form);
    if (trigger) {
      trigger.classList.add('composer__pick--locked');
      trigger.setAttribute('aria-disabled', 'true');
    }
  }

  function unlockFileInput(form) {
    if (!form) return;
    delete form.dataset.composerImageLocked;
    const { trigger } = getFileControl(form);
    if (trigger) {
      trigger.classList.remove('composer__pick--locked');
      trigger.removeAttribute('aria-disabled');
    }
  }

  function isFileInputLocked(form) {
    return form ? form.dataset.composerImageLocked === '1' : false;
  }

  function guardFileTrigger(form, event) {
    if (!isFileInputLocked(form)) return;
    if (event.type === 'keydown') {
      const key = event.key || '';
      if (key !== 'Enter' && key !== ' ' && key !== 'Spacebar') {
        return;
      }
    }
    event.preventDefault();
    event.stopPropagation();
  }

  global.CreatePostComposer = {
    init,
    setupForm,
    clearPreview,
    clearSelection
  };
})(window);
