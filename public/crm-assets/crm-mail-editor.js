(function () {
  function sync(editor) {
    var model = editor.getAttribute('data-model');
    if (!model || !window.Livewire) return;
    var root = editor.closest('[wire\\:id]');
    if (!root) return;
    var component = Livewire.find(root.getAttribute('wire:id'));
    if (!component) return;
    component.set(model, editor.innerHTML);
  }

  function exec(cmd, value) {
    document.execCommand(cmd, false, value ?? null);
  }

  function closestEditor(el) {
    var wrap = el.closest('.crm-rte');
    return wrap ? wrap.querySelector('.crm-rte-editor') : null;
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-rte-cmd]');
    if (!btn) return;
    e.preventDefault();
    var editor = closestEditor(btn);
    if (!editor) return;
    editor.focus();
    var cmd = btn.getAttribute('data-rte-cmd');
    var value = btn.getAttribute('data-rte-value');

    if (cmd === 'createLink') {
      var url = window.prompt('Enter link URL', 'https://');
      if (!url) return;
      exec('createLink', url);
    } else if (cmd === 'insertImage') {
      var img = window.prompt('Enter image URL', 'https://');
      if (!img) return;
      exec('insertImage', img);
    } else if (cmd === 'fontSize') {
      exec('fontSize', value || '3');
    } else if (cmd === 'formatBlock') {
      exec('formatBlock', value || 'p');
    } else {
      exec(cmd, value);
    }
    sync(editor);
  });

  document.addEventListener('change', function (e) {
    var sel = e.target.closest('[data-rte-select]');
    if (!sel) return;
    var editor = closestEditor(sel);
    if (!editor) return;
    editor.focus();
    var cmd = sel.getAttribute('data-rte-select');
    exec(cmd, sel.value);
    sync(editor);
  });

  document.addEventListener('input', function (e) {
    if (!e.target.classList || !e.target.classList.contains('crm-rte-editor')) return;
    sync(e.target);
  });

  document.addEventListener('paste', function (e) {
    if (!e.target.classList || !e.target.classList.contains('crm-rte-editor')) return;
    e.preventDefault();
    var text = (e.clipboardData || window.clipboardData).getData('text/plain');
    document.execCommand('insertText', false, text);
  });

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || !form.querySelector) return;
    form.querySelectorAll('.crm-rte-editor').forEach(sync);
  }, true);

  window.CrmMailEditor = {
    setHtml: function (selector, html) {
      var el = document.querySelector(selector);
      if (el) el.innerHTML = html || '';
    },
    clear: function (selector) {
      var el = document.querySelector(selector);
      if (el) el.innerHTML = '';
    },
    syncAll: function (root) {
      (root || document).querySelectorAll('.crm-rte-editor').forEach(sync);
    }
  };

  document.addEventListener('submit', function (e) {
    if (!e.target.closest) return;
    CrmMailEditor.syncAll(e.target);
  }, true);
})();
