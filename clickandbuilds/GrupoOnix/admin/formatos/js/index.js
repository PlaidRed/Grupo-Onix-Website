$(document).ready(function () {
  const folderRoot = $('.folder-root');

  $(document).on('click', '#toggle-edit-mode', function () {
		$('body').toggleClass('edit-mode');

		const isEdit = $('body').hasClass('edit-mode');
		$(this).text(isEdit ? '✅ Terminar edición' : '🔧 Editar');
	});

  $('#add-root-folder').on('click', function () {
    const name = prompt('Nombre de la nueva carpeta:');
    if (name) {
      folderRoot.append(createFolderElement(name));
    }
  });

  $(document).on('click', '.folder-toggle', function () {
    const folder = $(this).closest('.folder');
    folder.toggleClass('open');
  });

  // Agregar subcarpeta
  $(document).on('click', '.btn-add', function (e) {
    e.stopPropagation();
    const name = prompt('Nombre de la subcarpeta:');
    if (name) {
      const folder = $(this).closest('.folder');
      const ul = getOrCreateSubList(folder);
      ul.append(createFolderElement(name));
      folder.addClass('open');
    }
  });

  // Renombrar carpeta
  $(document).on('click', '.btn-rename', function (e) {
    e.stopPropagation();
    const label = $(this).closest('.folder').find('> .folder-toggle');
    const newName = prompt('Nuevo nombre:', label.text().replace('📁 ', ''));
    if (newName) {
      label.text(`📁 ${newName}`);
    }
  });

  // Eliminar carpeta
  $(document).on('click', '.btn-delete', function (e) {
    e.stopPropagation();
    if (confirm('¿Eliminar esta carpeta y su contenido?')) {
      $(this).closest('.folder').remove();
    }
  });

  // Agregar enlace
  $(document).on('click', '.btn-add-text', function (e) {
    e.stopPropagation();
    const label = prompt('Texto del enlace (ej: Documentación):');
    if (!label) return;

    const url = prompt('URL del enlace (ej: https://ejemplo.com):');
    if (!url || !isValidUrl(url)) {
      alert('La URL no es válida');
      return;
    }

    const folder = $(this).closest('.folder');
    const ul = getOrCreateSubList(folder);
    ul.append(`
      <li class="folder-text">
        🔗 <a href="${url}" target="_blank">${label}</a>
        <button class="btn-edit-link btn btn-sm btn-warning" title="Editar enlace">✎</button>
        <button class="btn-delete-link btn btn-sm btn-danger" title="Eliminar enlace">🗑️</button>
        <button class="btn-show-credentials btn btn-sm btn-info" title="Mostrar campos de usuario y contraseña">🔐</button>
        <div class="credentials-section" style="display: none; margin-top: 5px;">
			<input type="text" class="link-user form-control-sm" placeholder="Usuario">
			<button class="btn-copy-user btn btn-sm btn-light" title="Copiar usuario">📋</button><br>
			<input type="text" class="link-pass form-control-sm" placeholder="Contraseña">
			<button class="btn-copy-pass btn btn-sm btn-light" title="Copiar contraseña">📋</button><br>
			<button class="btn-lock-credentials btn btn-sm btn-secondary mt-1 edit-only" title="Bloquear campos de texto">🔒 Guardar</button>
        </div>
      </li>
    `);
    folder.addClass('open');
  });

  // Editar enlace
  $(document).on('click', '.btn-edit-link', function (e) {
    e.stopPropagation();
    const container = $(this).closest('.folder-text');
    const link = container.find('a');

    const currentText = link.text();
    const currentHref = link.attr('href');

    const newText = prompt('Nuevo texto del enlace:', currentText);
    if (!newText) return;

    const newHref = prompt('Nueva URL:', currentHref);
    if (!newHref || !isValidUrl(newHref)) {
      alert('La URL no es válida');
      return;
    }

    link.text(newText);
    link.attr('href', newHref);
  });

  // Eliminar enlace
  $(document).on('click', '.btn-delete-link', function (e) {
    e.stopPropagation();
    if (confirm('¿Eliminar este enlace?')) {
      $(this).closest('.folder-text').remove();
    }
  });

  // Mostrar/ocultar credenciales
  $(document).on('click', '.btn-show-credentials', function (e) {
    e.preventDefault();
    $(this).closest('.folder-text').find('.credentials-section').toggle();
  });

  // Bloquear o habilitar edición de credenciales
  $(document).on('click', '.btn-lock-credentials', function (e) {
    e.stopPropagation();
    const section = $(this).closest('.credentials-section');
    const userInput = section.find('.link-user');
    const passInput = section.find('.link-pass');

    const isDisabled = userInput.prop('disabled');

    if (!isDisabled) {
      userInput.prop('disabled', true);
      passInput.prop('disabled', true);
      $(this).html('🔓 Editar');
      $(this).attr('title', 'Habilitar edición');
    } else {
      userInput.prop('disabled', false);
      passInput.prop('disabled', false);
      $(this).html('🔒 Guardar');
      $(this).attr('title', 'Guardar y bloquear');
    }
  });

  // Copiar usuario
  $(document).on('click', '.btn-copy-user', function (e) {
    e.stopPropagation();
    const text = $(this).siblings('.link-user').val();
    navigator.clipboard.writeText(text);
  });

  // Copiar contraseña
  $(document).on('click', '.btn-copy-pass', function (e) {
    e.stopPropagation();
    const text = $(this).siblings('.link-pass').val();
    navigator.clipboard.writeText(text);
  });

  // Función para crear carpeta
  function createFolderElement(name) {
    return $(`
      <li class="folder">
        <span class="folder-toggle">📁 ${name}</span>
        <span class="folder-actions">
          <button class="btn-add btn btn-sm btn-success" title="Agregar subcarpeta">+</button>
          <button class="btn-add-text btn btn-sm btn-secondary" title="Agregar enlace">📝</button>
          <button class="btn-rename btn btn-sm btn-warning" title="Renombrar carpeta">✎</button>
          <button class="btn-delete btn btn-sm btn-danger" title="Eliminar carpeta">🗑️</button>
        </span>
      </li>
    `);
  }

  function getOrCreateSubList(folder) {
    let ul = folder.children('ul');
    if (ul.length === 0) {
      ul = $('<ul></ul>').appendTo(folder);
    }
    return ul;
  }

  function isValidUrl(string) {
    try {
      new URL(string);
      return true;
    } catch (_) {
      return false;
    }
  }
});