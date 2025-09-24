$(document).ready(function () {
  const $workspace = $('#folder-workspace');
  const workspaceEl = $workspace.length ? $workspace[0] : window;

  let sortableInstances = [];
  let nextOrder = 1; // Global variable to track next sort order

  $(document).on('click', '.folder-toggle', function (e) {
    e.stopPropagation();
    $(this).parent('li.folder').toggleClass('open');
  });

  addDropZoneStyles();

  $(document).on('click', '.btn-add', function () {
    const parentLi = $(this).closest('li.folder');
    const parentId = parentLi.data('id');

    showInputModal('Nombre de la subcarpeta:', function (folderName, unlock) {
      if (!folderName) {
        unlock();
        return;
      }

      const nextOrder = parentLi.children('ul').children('li.folder').length + 1;

      $.post('db/createFolder.php', { name: folderName, parent_id: parentId }, function (folderData) {
        if (folderData && folderData.success) {
          const newFolder = {
            id: folderData.id,
            name: folderData.name,
            parent_id: parentId,
            sort_order: nextOrder,
            children: []
          };

          let ul = parentLi.children('ul');
          if (ul.length === 0) {
            ul = $('<ul></ul>').appendTo(parentLi);
          }

          const li = buildSingleFolderElement(newFolder).hide();
          ul.append(li);
          li.slideDown('fast');
          parentLi.addClass('open');

          makeAllListsSortable(); // Re-initialize sortable for the new list
        } else {
          showAlert('Error creando subcarpeta: ' + (folderData?.msg || 'Error desconocido'));
        }
      }).fail(() => {
        alert('Error de conexión.');
      }).always(() => {
        unlock();
      });
    });
  });

  // Add this to your existing toggle edit mode function (replace the sortable instances part):
  $(document).on('click', '#toggle-edit-mode', function () {
     $('body').toggleClass('edit-mode');
     const isEdit = $('body').hasClass('edit-mode');
     $(this).text(isEdit ? '✅ Terminar edición' : '🔧 Editar');

     $('.folder-text .credentials-section input').prop('disabled', true);
     if (isEdit) {
         // Show edit elements
         $('.edit-only').show();
         $('.folder-text .btn-lock-credentials').show().html('🔓 Editar');
        
         // Show drag handles
         $('.drag-handle').show();
        
         // Show action buttons
         $('.link-actions').show();
         
         // Show root drop zone
         $('#root-drop-zone').show();
     } else {
         // Hide edit elements
         $('.edit-only').hide();
         $('.folder-text .btn-lock-credentials').hide();
        
         // Hide drag handles
         $('.drag-handle').hide();
        
         // Hide action buttons  
         $('.link-actions').hide();
         
         // Hide root drop zone
         $('#root-drop-zone').hide();
     }

     // Enable/Disable drag according to edit mode
     sortableInstances.forEach(s => s.option('disabled', !isEdit));
 });


  $('#add-root-folder').on('click', function () {
    showInputModal('Nombre de la nueva carpeta:', function (folderName, unlock) {
      if (!folderName) {
        unlock();
        return;
      }

      $.post('db/createFolder.php', { name: folderName, parent_id: null }, function (folderData) {
        if (folderData.success) {
          const newFolder = {
            id: folderData.id,
            name: folderName,
            parent_id: null,
            sort_order: nextOrder++,
            children: []
          };

          let ul = $('.folder-root > ul');
          if (ul.length === 0) {
            ul = $('<ul></ul>').appendTo('.folder-root');
          }

          const tree = buildTreeFromDB([newFolder]);
          ul.append(tree.children());
        } else {
          showAlert('Error creando carpeta: ' + (folderData.msg || 'Error desconocido'));
        }
        unlock();
      }, 'json').fail(() => {
        alert('Error de comunicación con el servidor');
        unlock();
      });
    });
  });

  $(document).on('click', '.btn-rename', function (e) {
    e.stopPropagation();
    const folderEl = $(this).closest('.folder');
    const folderId = folderEl.data('id');
    
    // More specific selector for the folder toggle text
    const folderToggle = folderEl.find('> .folder-content > .folder-toggle').first();
    const currentName = folderToggle.text().replace('📁 ', '').trim();

    showInputModal('Nuevo nombre de la carpeta:', function (newName, unlock) {
      if (!newName || newName.trim() === currentName.trim()) {
        unlock();
        return;
      }

      $.post('db/renameFolder.php', { id: folderId, newName: newName }, function (res) {
        if (res.success) {
          // Update the folder toggle text immediately
          folderToggle.text(`📁 ${newName}`);
          console.log('Folder renamed successfully:', { id: folderId, newName: newName });
        } else {
          showAlert('No se pudo renombrar la carpeta: ' + (res.msg || 'Error desconocido'));
        }
        unlock();
      }, 'json').fail(() => {
        alert('Error de conexión con el servidor');
        unlock();
      });
    }, currentName);
  });

  $(document).on('click', '.btn-delete', function (e) {
    e.stopPropagation();
    const folderEl = $(this).closest('.folder');
    const folderId = folderEl.data('id');

    showConfirmModal('¿Eliminar esta carpeta y su contenido?', function (confirmed) {
      if (!confirmed) return;

      $.post('db/deleteFolder.php', { id: folderId }, function (res) {
        if (res.success) {
          folderEl.remove();
        } else {
          showAlert('No se pudo eliminar la carpeta: ' + (res.msg || 'Error desconocido'));
        }
      }, 'json').fail(() => {
        alert('Error de conexión con el servidor');
      });
    });
  });

  $(document).on('click', '.btn-add-text', function (e) {
    e.stopPropagation();
    const folder = $(e.target).closest('.folder');
    const folderId = folder.data('id');

    showInputModal('Texto del enlace (ej: Documentación):', function (label, unlock1) {
      if (!label) {
        unlock1();
        return;
      }

      showInputModal('URL del enlace (ej: https://ejemplo.com):', function (url, unlock2) {
        if (!url || !isValidUrl(url)) {
          showAlert('La URL no es válida');
          unlock2();
          return;
        }

        const username = '';
        const password = '';
        const nextOrder = folder.find('ul > li.folder-text').length + 1;
        fetch('db/addLink.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ folder_id: folderId, label, url, username, password, sort_order : nextOrder })
        }).then(res => res.json()).then(data => {
          if (data.success) {
            const ul = getOrCreateSubList(folder);
            ul.append(buildLinkElement(data.id, label, url, username, password));
              if ($('body').hasClass('edit-mode')) {
                ul.find('.folder-text:last .btn-lock-credentials').show();
              }
            folder.addClass('open');
          } else {
            showAlert('Error al guardar el enlace: ' + (data.msg || 'Error desconocido'));
          }
          unlock2(); // Close the second modal
          unlock1(); // Close the first modal
        }).catch(() => {
          alert('Error de conexión con el servidor');
          unlock2();
          unlock1();
        });
      });
    });
  });


  $(document).on('click', '.btn-edit-link', function (e) {
    e.stopPropagation();
    const container = $(this).closest('.folder-text');
    const link = container.find('a');
    const currentText = link.text();
    const currentHref = link.attr('href');
    const linkId = container.data('id');

    showInputModal('Nuevo texto del enlace:', function (newText, unlock1) {
      if (!newText) return unlock1();

      showInputModal('Nueva URL del enlace:', function (newHref, unlock2) {
        if (!newHref || !isValidUrl(newHref)) {
          showAlert('La URL no es válida');
          unlock2();
          return;
        }

        // Update DOM
        link.text(newText);
        link.attr('href', newHref);

        // Update DB
        fetch('db/updateLink.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: linkId, label: newText, url: newHref })
        }).then(res => res.json()).then(data => {
          if (!data.success) {
            showAlert('Error al actualizar el enlace: ' + (data.msg || 'Error desconocido'));
          }
          unlock2();
        }).catch(err => {
          showAlert('Error de conexión');
          unlock2();
        });

      }, currentHref);  // <-- prefill URL input here

    }, currentText);  // <-- prefill text input here
  });



  $(document).on('click', '.btn-delete-link', function (e) {
    e.stopPropagation();
    const container = $(this).closest('.folder-text');
    const linkId = container.data('id');
    if (!linkId) return alert('No se pudo identificar el enlace.');
    showConfirmModal('¿Eliminar este enlace?', function (confirmed) {
      if (!confirmed) return;
      fetch('db/deleteLink.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: linkId })
      }).then(res => res.json()).then(data => {
        data.success ? container.remove() : showAlert('Error al eliminar el enlace: ' + (data.msg || 'Error desconocido'));
      }).catch(() => alert('Error de conexión con el servidor'));
    });
  });

  // Toggle credentials section visibility
  $(document).on('click', '.btn-show-credentials', function (e) {
    e.preventDefault();
    $(this).closest('.folder-text').find('.credentials-section').toggle();
  });

  function showCustomModal(message) {
    const modal = document.getElementById('custom-modal');
    const messageEl = document.getElementById('custom-modal-message');

    if (modal && messageEl) {
      messageEl.textContent = message;
      modal.style.display = 'block';

      const closeBtn = document.getElementById('custom-modal-close');
      if (closeBtn) {
        closeBtn.onclick = () => {
          modal.style.display = 'none';
        };
      }

      // Auto-close after 3 seconds
      setTimeout(() => {
        modal.style.display = 'none';
      }, 3500);
    }
  }

  $(document).on('click', '.btn-lock-credentials', function () {
    const $btn = $(this);
    const linkItem = $btn.closest('li.folder-text');
    const id = linkItem.data('id');
    const usernameInput = linkItem.find('.link-user');
    const passwordInput = linkItem.find('.link-pass');

    // If currently disabled, switch to edit mode (unlock)
    const isEditing = usernameInput.prop('disabled');

    if (isEditing) {
      usernameInput.prop('disabled', false);
      passwordInput.prop('disabled', false);
      passwordInput.attr('type', 'text'); // Show password text
      $btn.html('🔒 Guardar');
    } else {
      const username = usernameInput.val().trim();
      const password = passwordInput.val().trim();

      fetch('db/updateLink.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id, username, password })
      })
        .then(res => res.json())
        .then(response => {
          if (response.success) {
            showCustomModal("Credenciales guardadas correctamente.");
            usernameInput.prop('disabled', true);
            passwordInput.prop('disabled', true);
            passwordInput.attr('type', 'password'); // Hide password text
            $btn.html('🔓 Editar');
          } else {
            showCustomModal("Error: " + response.msg);
          }
        })
        .catch(err => {
          console.error(err);
          showCustomModal("Error inesperado al guardar.");
        });
      } 
  });

  
  // For input-modal
  $('#input-modal').on('click', function (e) {
    if (e.target !== this) return;

    $('#input-modal').fadeOut(() => {
      resetModalState();
    });
  });

  // For custom-modal (alert)
  $('#custom-modal').on('click', function (e) {
    if (e.target === this) {
      $(this).fadeOut();
    }
  });


  // Copy buttons
  $(document).on('click', '.btn-copy-user', function () {
    navigator.clipboard.writeText($(this).siblings('.link-user').val());
  });
  $(document).on('click', '.btn-copy-pass', function () {
    navigator.clipboard.writeText($(this).siblings('.link-pass').val());
  });

  fetch('db/getFoldersAndLinks.php')
    .then(response => {
        if (!response.ok) throw new Error('Error en la red');
        return response.json();
    })
    .then(data => {
        if (!data.success) throw new Error(data.msg || 'Error en los datos');
        
        console.log('Datos recibidos:', data);
        
        $('.folder-root').empty().append(buildTreeFromDB(data.folders, data.links));
        makeAllListsSortable();
        
        // Initialize drop zones after everything is set up
        setTimeout(() => {
            initializeFolderDropZones();
            initializeRootDropZone(); // Initialize the new root drop zone
            console.log('Drop zones initialized');
        }, 100);
    })
    .catch(error => {
        console.error('Error al cargar datos:', error);
        showAlert('Error al cargar la estructura: ' + error.message);
        setTimeout(() => location.reload(), 2000);
    });



  function buildLinkElement(id, label, url, username = '', password = '', folder = null) {
    const showCredentials = (username || password) ? 'block' : 'none';

    // Build the folder path (walk up parents)
    let pathParts = [];
    let current = folder;
    while (current) {
      pathParts.unshift(current.name);
      current = current.parent; // you'll set .parent in buildTreeFromDB
    }
    pathParts.push(label);

    const dataTrack = pathParts
      .map(p => p.normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "") // remove accents
        .replace(/[^a-z0-9]+/gi, "_")    // replace spaces/symbols
        .toLowerCase()
      )
      .join(">");

    return `<li class="folder-text" data-id="${id}">
      <span class="drag-handle edit-only" style="display: none; cursor: move;" title="Arrastrar para mover">☰</span>
      🔗 <a href="${url}" target="_blank" data-track="${dataTrack}">${label}</a>
      <span class="link-actions edit-only" style="display: none;">
        <button class="btn-edit-link btn btn-sm btn-warning" title="Editar enlace">✎</button>
        <button class="btn-delete-link btn btn-sm btn-danger" title="Eliminar enlace">🗑️</button>
      </span>
      <button class="btn-show-credentials btn btn-sm btn-info" title="Mostrar campos de usuario y contraseña">🔐</button>
      <div class="credentials-section" style="display: ${showCredentials}; margin-top: 5px;">
        <input type="text" class="link-user form-control-sm" placeholder="Usuario" value="${username}" disabled>
        <button class="btn-copy-user btn btn-sm btn-light" title="Copiar usuario">📋</button><br>
        <input type="password" class="link-pass form-control-sm" placeholder="Contraseña" value="${password}" disabled>
        <button class="btn-copy-pass btn btn-sm btn-light" title="Copiar contraseña">📋</button><br>
        <button class="btn-lock-credentials btn btn-sm btn-secondary mt-1 edit-only" style="display: none;" title="Guardar y bloquear">🔒 Guardar</button>
      </div>
    </li>`;
  }

  $('#input-modal').addClass('confirm-mode');

  function showConfirmModal(message, callback) {
    
    $('#input-modal-input').hide();
    $('#input-modal-message').text(message);
    $('#input-modal').fadeIn();

    $('#input-modal-accept').off('click').on('click', function () {
      $('#input-modal').fadeOut(() => {
        callback(true);
        resetModalState();
      });
    });

    $('#input-modal-cancel').off('click').on('click', function () {
      $('#input-modal').fadeOut(() => {
        callback(false);
        resetModalState();
      });
    });
  }

  function showInputModal(message, callback, prefill = '') {
    modalDismissedByClickOff = false;

    $('#input-modal-message').text(message);
    const inputField = $('#input-modal-input');
    inputField.val(prefill).show();
    $('#input-modal').fadeIn(() => {
      inputField.focus().select(); // Focus and select after fade-in
    });

    const $acceptBtn = $('#input-modal-accept');
    const $cancelBtn = $('#input-modal-cancel');
    let isLocked = false;

    $('#input-modal-input').off('keydown').on('keydown', function (e) {
      if (e.key === 'Enter') $acceptBtn.trigger('click');
    });

    function unlock() {
      $('#input-modal').fadeOut(() => {
        inputField.val('').hide();
        resetModalState();
      });
    }

    $acceptBtn
      .removeClass().addClass('btn btn-sm btn-success')
      .prop('disabled', false)
      .text('Aceptar')
      .off('click')
      .on('click', function () {
        if (isLocked) return;

        const inputValue = inputField.val().trim();
        if (!inputValue) return;

        isLocked = true;
        $acceptBtn.prop('disabled', true).text('Creando...');
        $cancelBtn.prop('disabled', true);

        callback(inputValue, unlock);
      });

    $cancelBtn
      .removeClass().addClass('btn btn-sm btn-danger')
      .prop('disabled', false)
      .off('click')
      .on('click', function () {
        unlock();
        callback(null, () => {});
      });
  }


  function resetModalState() {
    $('#input-modal-input').val('').hide();
    $('#input-modal-message').text('');
    $('#input-modal-accept').prop('disabled', false).text('Aceptar');
    $('#input-modal-cancel').prop('disabled', false);
  }


  function showAlert(message) {
    $('#custom-modal-message').text(message);
    $('#custom-modal').fadeIn();
  }

  $(document).on('click', '#custom-modal-close', function () {
    $('#custom-modal').fadeOut();
  });

  function buildSingleFolderElement(folder) {
    return $(`<li class="folder" data-id="${folder.id}">
      <div class="folder-content">
        <span class="drag-handle edit-only" style="display: none; cursor: move;">☰</span>
        <span class="folder-toggle">📁 ${folder.name}</span>
        <span class="folder-actions">
          <button class="btn-add btn btn-sm btn-success" title="Agregar subcarpeta">+</button>
          <button class="btn-add-text btn btn-sm btn-secondary" title="Agregar enlace">📝</button>
          <button class="btn-rename btn btn-sm btn-warning" title="Renombrar carpeta">✎</button>
          <button class="btn-delete btn btn-sm btn-danger" title="Eliminar carpeta">🗑️</button>
        </span>
      </div>
      <div class="folder-drop-zone" data-folder-id="${folder.id}">
        <div class="drop-zone-content">
          <span class="drop-zone-icon">📂</span>
          <span class="drop-zone-text">Arrastra aquí</span>
        </div>
      </div>
    </li>`);
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

  function buildTreeFromDB(folders, links = []) {
    const openFolders = [];
    $('.folder.open').each(function() {
        openFolders.push($(this).data('id'));
    });

    if (!Array.isArray(folders) || !Array.isArray(links)) {
        console.error('Datos inválidos:', { folders, links });
        return $('<ul></ul>');
    }

    // Crear mapa de carpetas y raíces
    const folderMap = {};
    const rootFolders = [];

    folders.forEach(folder => {
        // Asegurar valores válidos
        folder.children = [];
        folder.parent_id = folder.parent_id || null;
        folder.sort_order = folder.sort_order || folder.id;
        folderMap[folder.id] = folder;
    });

    // Construir jerarquía
    folders.forEach(folder => {
        if (folder.parent_id && folderMap[folder.parent_id]) {
            folderMap[folder.parent_id].children.push(folder);
        } else {
            rootFolders.push(folder);
        }
    });

    // Ordenar hijos (el servidor ya envía ordenados, pero por si acaso)
    Object.values(folderMap).forEach(folder => {
        folder.children.sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id));
    });

    // Procesar links
    const linksByFolder = {};
    links.forEach(link => {
        link.folder_id = link.folder_id || null;
        link.sort_order = link.sort_order || link.id;
        
        if (!linksByFolder[link.folder_id]) {
            linksByFolder[link.folder_id] = [];
        }
        linksByFolder[link.folder_id].push(link);
    });

    // Función para construir el DOM
    function createDOM(folder) {
        const li = buildSingleFolderElement(folder);
        
        if (folder.children.length > 0 || linksByFolder[folder.id]?.length > 0) {
            const ul = $('<ul></ul>');
            
            // Agregar carpetas hijas (ya ordenadas)
            folder.children.forEach(child => {
                child.parent = folder; // para construir path en buildLinkElement
                ul.append(createDOM(child));
            });
            
            // Agregar links (ordenados)
            if (linksByFolder[folder.id]) {
                linksByFolder[folder.id]
                    .sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id))
                    .forEach(link => {
                        ul.append(buildLinkElement(
                            link.id, 
                            link.label, 
                            link.url, 
                            link.username || '', 
                            link.password || ''
                        ));
                    });
            }
            
            li.append(ul);
        }
        
        return li;
    }

    // Construir árbol completo
    const ul = $('<ul></ul>');
      rootFolders
          .sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id))
          .forEach(folder => {
              ul.append(createDOM(folder));
          });

          // Después de construir el árbol, restaurar estados abiertos
    setTimeout(() => {
      openFolders.forEach(id => {
          const $folder = $(`.folder[data-id="${id}"]`);
          $folder.addClass('open');
          $folder.children('ul').css('display', 'block');
      });
    }, 50);

      



      
      return ul;
  }

  


  function makeAllListsSortable() {
    // Eliminar eventos antiguos para evitar duplicados
    $('.folder-toggle').off('click.toggleFolder');
    
    // Initialize drop zones first
    initializeFolderDropZones();
    
    $('ul:not(.sortable-initialized)').each(function() {
        makeUlSortable(this);
    });
    
    // Asignar eventos de toggle iniciales
    assignFolderToggleEvents();
  }

// Función auxiliar para manejar eventos de toggle
function assignFolderToggleEvents() {
    $('.folder-toggle').off('click.toggleFolder').on('click.toggleFolder', function(e) {
        e.stopPropagation();
        const $folder = $(this).closest('li.folder');
        $folder.toggleClass('open');
        const $ul = $folder.children('ul');
        if ($folder.hasClass('open')) {
            $ul.show();
        } else {
            $ul.hide();
        }

    });
}


  function normalizeString(str) {
  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
  }

  $('#link-search').on('input', function () {
    const rawQuery = $(this).val().trim();
    const query = normalizeString(rawQuery);

    if (query === '') {
      // Show everything when search is cleared
      $('.folder, .folder-text').show();
      $('.folder').removeClass('open');
      return;
    }

    // Hide everything initially
    $('.folder, .folder-text').hide();
    $('.folder').removeClass('open');

    $('.folder-text').each(function () {
      const $linkItem = $(this);
      const $link = $linkItem.find('a');

      const label = $link.text();
      const url = $link.attr('href');

      const normLabel = normalizeString(label);
      const normUrl = normalizeString(url);

      const matches = normLabel.includes(query) || normUrl.includes(query);

      // Remove previous highlights
      $link.html(label);

      if (matches) {
        $linkItem.show();

        // Highlight match in label
        if (normLabel.includes(query)) {
          const start = normLabel.indexOf(query);
          const end = start + query.length;

          const highlighted =
            label.substring(0, start) +
            `<span class="highlight">` +
            label.substring(start, end) +
            `</span>` +
            label.substring(end);

          $link.html(highlighted);
        }

        // Show all parent folders
        $linkItem.parents('li.folder').show().addClass('open');
      }
    });
  });



  $('#clear-search').on('click', function () {
      $('#link-search').val('').trigger('input');
  });

  function initializeFolderDropZones() {
    console.log('Initializing folder drop zones...');
    
    // Clear existing drop zones to prevent duplicates
    $('.folder-drop-zone').each(function() {
        if ($(this).hasClass('sortable-initialized')) {
            const existingInstance = sortableInstances.find(s => s.el === this);
            if (existingInstance) {
                existingInstance.destroy();
                sortableInstances = sortableInstances.filter(s => s !== existingInstance);
            }
        }
    });

    let dropZoneCount = 0;
    $('.folder-drop-zone').each(function() {
        const $dropZone = $(this);
        const folderId = $dropZone.data('folder-id');
        
        if (!folderId) {
            console.warn('Drop zone found without folder-id:', this);
            return;
        }
        
        console.log(`Setting up drop zone ${dropZoneCount++} for folder:`, folderId);
        
        $dropZone.removeClass('sortable-initialized').addClass('sortable-initialized');
        
        const sortable = new Sortable($dropZone[0], {
            group: {
                name: 'shared',
                pull: false,  // Don't allow pulling from drop zones
                put: true     // ACCEPT items from regular lists
            },
            animation: 150,
            disabled: !$('body').hasClass('edit-mode'),
            sort: false, // Drop zone doesn't allow internal sorting

            scroll: true,
            scrollSensitivity: 60,
            scrollSpeed: 15,
            fallbackOnBody: false,
            scrollFn: function(offsetX, offsetY, originalEvent) {
                if (workspaceEl === window) {
                    if (offsetY) window.scrollBy(0, offsetY);
                    if (offsetX) window.scrollBy(offsetX, 0);
                } else {
                    if (offsetY) workspaceEl.scrollTop += offsetY;
                    if (offsetX) workspaceEl.scrollLeft += offsetX;
                }
            },
            
            onMove: function(evt) {
                const $draggedItem = $(evt.dragged);
                const draggedId = $draggedItem.data('id');
                const draggedType = $draggedItem.hasClass('folder') ? 'folder' : 'link';
                
                console.log(`onMove DROP ZONE: ${draggedType} ${draggedId} -> folder ${folderId}`);
                
                // Prevent dropping folder into itself or its descendants
                if ($draggedItem.hasClass('folder')) {
                    if (folderId == draggedId) {
                        console.log('Preventing folder from dropping into itself');
                        return false;
                    }
                    
                    const $targetFolder = $(`.folder[data-id="${folderId}"]`);
                    if (isDescendantOf($targetFolder, draggedId)) {
                        console.log('Preventing folder from dropping into descendant');
                        return false;
                    }
                }
                
                return true; // Allow the move
            },
            
            onAdd: function(evt) {
                console.log('SUCCESS: Item dropped in folder drop zone!', {
                    item: evt.item,
                    folderId: folderId
                });
                
                const $draggedItem = $(evt.item);
                const draggedId = $draggedItem.data('id');
                const draggedType = $draggedItem.hasClass('folder') ? 'folder' : 'link';
                
                // Remove item from drop zone immediately to prevent visual issues
                $draggedItem.detach();
                
                // Show loading state
                showCustomModal('Moviendo elemento...');
                
                // Process the drop
                if (draggedType === 'link') {
                    handleLinkDrop(draggedId, folderId, $draggedItem);
                } else {
                    handleFolderDrop(draggedId, folderId, $draggedItem);
                }
            }
        });
        
        sortableInstances.push(sortable);
    });
  }

  // Initialize the root drop zone for moving items back to root level
  function initializeRootDropZone() {
    console.log('Initializing root drop zone...');
    
    // Create the root drop zone element if it doesn't exist
    if ($('#root-drop-zone').length === 0) {
        const rootDropZoneHTML = `
            <div id="root-drop-zone" class="root-drop-zone edit-only" style="display: none;">
                <div class="root-drop-zone-content">
                    <span class="root-drop-zone-text">Mover al nivel raíz</span>
                    <small class="root-drop-zone-help">Arrastra carpetas o enlaces aquí para sacarlos de subcarpetas</small>
                </div>
            </div>
        `;
        
        $('.folder-root').before(rootDropZoneHTML);
    }
    
    const $rootDropZone = $('#root-drop-zone');
    
    // Remove existing sortable instance if it exists
    const existingInstance = sortableInstances.find(s => s.el === $rootDropZone[0]);
    if (existingInstance) {
        existingInstance.destroy();
        sortableInstances = sortableInstances.filter(s => s !== existingInstance);
    }
    
    const rootSortable = new Sortable($rootDropZone[0], {
        group: {
            name: 'shared',
            pull: false,  // Don't allow pulling from root drop zone
            put: true     // ACCEPT items from regular lists
        },
        animation: 150,
        disabled: !$('body').hasClass('edit-mode'),
        sort: false, // Root drop zone doesn't allow internal sorting

        scroll: true,
        scrollSensitivity: 60,
        scrollSpeed: 15,
        fallbackOnBody: false,
        scrollFn: function(offsetX, offsetY, originalEvent) {
            if (workspaceEl === window) {
                if (offsetY) window.scrollBy(0, offsetY);
                if (offsetX) window.scrollBy(offsetX, 0);
            } else {
                if (offsetY) workspaceEl.scrollTop += offsetY;
                if (offsetX) workspaceEl.scrollLeft += offsetX;
            }
        },
        
        onMove: function(evt) {
            const $draggedItem = $(evt.dragged);
            console.log('onMove ROOT DROP ZONE: allowing move to root');
            return true; // Allow all moves to root
        },
        
        onAdd: function(evt) {
            console.log('SUCCESS: Item dropped in root drop zone!');
            
            const $draggedItem = $(evt.item);
            const draggedId = $draggedItem.data('id');
            const draggedType = $draggedItem.hasClass('folder') ? 'folder' : 'link';
            
            // Remove item from drop zone immediately
            $draggedItem.detach();
            
            // Show loading state
            showCustomModal('Moviendo al nivel raíz...');
            
            // Process the drop
            if (draggedType === 'link') {
                handleLinkMoveToRoot(draggedId, $draggedItem);
            } else {
                handleFolderMoveToRoot(draggedId, $draggedItem);
            }
        }
    });
    
    sortableInstances.push(rootSortable);
  }

  // Helper function to make a single ul sortable
  function makeUlSortable(ul) {
    if (ul.classList.contains('sortable-initialized')) return;
    
    ul.classList.add('sortable-initialized');
    
    let draggedElement = null;
    let draggedFromParent = null;
    let draggedFromIndex = -1;
    
    const sortable = new Sortable(ul, {
        animation: 60,
        handle: '.folder-toggle, a, .drag-handle',
        disabled: !$('body').hasClass('edit-mode'),
        group: {
            name: 'shared',
            pull: true,   // Allow pulling from regular lists
            put: false    // Don't allow dropping into regular lists
        },

        scroll: true,
        scrollSensitivity: 200,
        scrollSpeed: 15,
        fallbackOnBody: false,
        scrollFn: function(offsetX, offsetY, originalEvent) {
            if (workspaceEl === window) {
                if (offsetY) window.scrollBy(0, offsetY);
                if (offsetX) window.scrollBy(offsetX, 0);
            } else {
                if (offsetY) workspaceEl.scrollTop += offsetY;
                if (offsetX) workspaceEl.scrollLeft += offsetX;
            }
        },
        
        onStart: function(evt) {
            // Store the original element and its position
            draggedElement = evt.item.cloneNode(true);
            draggedFromParent = evt.from;
            draggedFromIndex = Array.from(evt.from.children).indexOf(evt.item);
            
            $('.folder-drop-zone, #root-drop-zone').addClass('drop-ready');
            $(evt.item).addClass('dragging');
        },

        // Block ALL moves except to drop zones
        onMove: function(evt) {
            const isDropZone = $(evt.to).hasClass('folder-drop-zone') || $(evt.to).hasClass('root-drop-zone');
            return isDropZone;
        },

        onEnd: function(evt) {
            const isDropZone = $(evt.to).hasClass('folder-drop-zone') || $(evt.to).hasClass('root-drop-zone');
            
            // If not dropped in a drop zone, force restore to original position
            if (!isDropZone && evt.from !== evt.to) {
                // Prevent SortableJS from doing its own placement
                evt.item.remove();
                
                // Restore to original position
                if (draggedFromIndex === 0) {
                    $(draggedFromParent).prepend(draggedElement);
                } else if (draggedFromIndex >= draggedFromParent.children.length) {
                    $(draggedFromParent).append(draggedElement);
                } else {
                    $(draggedFromParent.children[draggedFromIndex]).before(draggedElement);
                }
            }
            
            // Clean up
            $('.folder-drop-zone, #root-drop-zone').removeClass('drop-ready');
            $(evt.item).removeClass('dragging');
            draggedElement = null;
            draggedFromParent = null;
            draggedFromIndex = -1;
        }
    });
    
    sortableInstances.push(sortable);
  }

  function isDescendantOf($folder, ancestorId) {
    let $current = $folder.parent().closest('li.folder');
    while ($current.length) {
        if ($current.data('id') == ancestorId) {
            return true;
        }
        $current = $current.parent().closest('li.folder');
    }
    return false;
  }

  function handleLinkDrop(linkId, targetFolderId, $linkElement) {
    console.log(`Moving link ${linkId} to folder ${targetFolderId}`);
    
    // First, verify the target folder exists and get new sort order
    fetch('db/verifyFolder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ folder_id: targetFolderId })
    })
    .then(response => response.json())
    .then(folderData => {
        if (!folderData.success) {
            throw new Error(folderData.msg || 'Carpeta no encontrada');
        }
        
        console.log('Folder verified, updating link...');
        
        // Update link's folder_id using your existing updateLink.php
        return fetch('db/updateLink.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: linkId, 
                folder_id: targetFolderId,
                sort_order: folderData.next_sort_order || 1
            })
        });
    })
    .then(response => response.json())
    .then(updateData => {
        if (!updateData.success) {
            throw new Error(updateData.msg || 'Error al mover el enlace');
        }
        
        console.log('Link updated in database, updating DOM...');
        
        // Now update the DOM
        const $targetFolder = $(`.folder[data-id="${targetFolderId}"]`);
        let $targetUl = $targetFolder.children('ul');
        
        // Create ul if needed
        if ($targetUl.length === 0) {
            $targetUl = $('<ul></ul>').appendTo($targetFolder);
            makeUlSortable($targetUl[0]);
        }
        
        // Add link after all folders
        $targetUl.append($linkElement);
        
        // Update data attributes
        $linkElement.data('parent_id', targetFolderId);
        
        // Open target folder and show its contents
        $targetFolder.addClass('open');
        $targetUl.show();
        
        // Show success message
        const folderName = $targetFolder.find('.folder-toggle').text().replace('📁 ', '');
        showCustomModal(`Enlace movido correctamente`);
        
        console.log('Link move completed successfully');
    })
    .catch(error => {
        console.error('Error moving link:', error);
        showAlert('Error al mover el enlace: ' + error.message);
        
        // Restore the element to its original position if possible
        // You might want to reload the page or restore from backup
        location.reload();
    });
  }

  // Handle moving links to root level
  function handleLinkMoveToRoot(linkId, $linkElement) {
    console.log(`Moving link ${linkId} to root level`);
    
    // Update link's folder_id to null (root level)
    fetch('db/updateLink.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            id: linkId, 
            folder_id: null, // null means root level
            sort_order: 1 // You might want to calculate this properly
        })
    })
    .then(response => response.json())
    .then(updateData => {
        if (!updateData.success) {
            throw new Error(updateData.msg || 'Error al mover el enlace al nivel raíz');
        }
        
        console.log('Link moved to root in database, updating DOM...');
        
        // Find or create the root ul
        let $rootUl = $('.folder-root > ul');
        if ($rootUl.length === 0) {
            $rootUl = $('<ul></ul>').appendTo('.folder-root');
            makeUlSortable($rootUl[0]);
        }
        
        // Add link to root level (after all folders)
        const $rootFolders = $rootUl.children('li.folder');
        if ($rootFolders.length > 0) {
            $rootFolders.last().after($linkElement);
        } else {
            $rootUl.append($linkElement);
        }
        
        // Update data attributes
        $linkElement.data('parent_id', null);
        
        // Show success message
        showCustomModal('Enlace movido al nivel raíz correctamente');
        
        console.log('Link move to root completed successfully');
    })
    .catch(error => {
        console.error('Error moving link to root:', error);
        showAlert('Error al mover el enlace al nivel raíz: ' + error.message);
        location.reload();
    });
  }


  function handleFolderDrop(folderId, targetFolderId, $folderElement) {
    console.log(`Moving folder ${folderId} to folder ${targetFolderId}`);
    
    // First, verify the target folder exists
    fetch('db/verifyFolder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ folder_id: targetFolderId })
    })
    .then(response => response.json())
    .then(folderData => {
        if (!folderData.success) {
            throw new Error(folderData.msg || 'Carpeta destino no encontrada');
        }
        
        console.log('Target folder verified, updating folder parent...');
        
        // Use your existing createFolder.php with move parameters
        const formData = new FormData();
        formData.append('folder_id', folderId);
        formData.append('parent_id', targetFolderId);
        formData.append('sort_order', folderData.next_folder_sort_order || 1);
        
        return fetch('db/createFolder.php', {
            method: 'POST',
            body: formData
        });
    })
    .then(response => response.json())
    .then(updateData => {
        if (!updateData.success) {
            throw new Error(updateData.msg || 'Error al mover la carpeta');
        }
        
        console.log('Folder updated in database, updating DOM...');
        
        // Now update the DOM
        const $targetFolder = $(`.folder[data-id="${targetFolderId}"]`);
        let $targetUl = $targetFolder.children('ul');
        
        // Create ul if needed
        if ($targetUl.length === 0) {
            $targetUl = $('<ul></ul>').appendTo($targetFolder);
            makeUlSortable($targetUl[0]);
        }
        
        // Add folder before links
        const $existingFolders = $targetUl.children('li.folder');
        if ($existingFolders.length > 0) {
            $existingFolders.last().after($folderElement);
        } else {
            const $firstLink = $targetUl.children('li.folder-text').first();
            if ($firstLink.length > 0) {
                $firstLink.before($folderElement);
            } else {
                $targetUl.append($folderElement);
            }
        }
        
        // Update data attributes
        $folderElement.data('parent_id', targetFolderId);
        
        // Open target folder and show its contents
        $targetFolder.addClass('open');
        $targetUl.show();
        
        // Show success message
        const folderName = $targetFolder.find('.folder-toggle').text().replace('📁 ', '');
        showCustomModal(`Carpeta movida correctamente`);
        
        console.log('Folder move completed successfully');
    })
    .catch(error => {
        console.error('Error moving folder:', error);
        showAlert('Error al mover la carpeta: ' + error.message);
        
        // Restore the element to its original position if possible
        location.reload();
    });
  }

  // Handle moving folders to root level
  function handleFolderMoveToRoot(folderId, $folderElement) {
    console.log(`Moving folder ${folderId} to root level`);
    
    // Use your existing createFolder.php to update parent_id to null
    const formData = new FormData();
    formData.append('folder_id', folderId);
    formData.append('parent_id', null); // null means root level
    formData.append('sort_order', 1); // You might want to calculate this properly
    
    fetch('db/createFolder.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(updateData => {
        if (!updateData.success) {
            throw new Error(updateData.msg || 'Error al mover la carpeta al nivel raíz');
        }
        
        console.log('Folder moved to root in database, updating DOM...');
        
        // Find or create the root ul
        let $rootUl = $('.folder-root > ul');
        if ($rootUl.length === 0) {
            $rootUl = $('<ul></ul>').appendTo('.folder-root');
            makeUlSortable($rootUl[0]);
        }
        
        // Add folder to root level (before any root-level links)
        const $rootLinks = $rootUl.children('li.folder-text');
        if ($rootLinks.length > 0) {
            $rootLinks.first().before($folderElement);
        } else {
            $rootUl.append($folderElement);
        }
        
        // Update data attributes
        $folderElement.data('parent_id', null);
        
        // Show success message
        showCustomModal('Carpeta movida al nivel raíz correctamente');
        
        console.log('Folder move to root completed successfully');
    })
    .catch(error => {
        console.error('Error moving folder to root:', error);
        showAlert('Error al mover la carpeta al nivel raíz: ' + error.message);
        location.reload();
    });
  }

  if (!document.getElementById('drop-zone-styles')) {
    const style = document.createElement('style');
    style.id = 'drop-zone-styles';
    style.textContent = dropZoneCSS;
    document.head.appendChild(style);
  }

  function addDropZoneStyles() {
    if (!document.getElementById('drop-zone-styles')) {
        const style = document.createElement('style');
        style.id = 'drop-zone-styles';
        style.textContent = `
          .folder-drop-zone {
              min-height: 40px;
              margin: 5px 10px;
              border: 2px dashed transparent;
              border-radius: 5px;
              transition: all 0.2s ease;
              display: none;
          }

          .edit-mode .folder-drop-zone {
              display: block;
              border-color: #dee2e6;
          }

          .folder-drop-zone.drop-ready {
              border-color: #007bff !important;
              background-color: rgba(0, 123, 255, 0.1);
              transform: scale(1.02);
              box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
          }

          .folder-drop-zone:hover.drop-ready {
              border-color: #0056b3 !important;
              background-color: rgba(0, 123, 255, 0.2);
          }

          .drop-zone-content {
              display: flex;
              align-items: center;
              justify-content: center;
              height: 100%;
              color: #6c757d;
              font-size: 12px;
              padding: 8px;
          }

          .drop-zone-content .drop-zone-icon {
              margin-right: 5px;
          }

          .folder-drop-zone.drop-ready .drop-zone-content {
              color: #007bff;
              font-weight: bold;
          }

          /* ROOT DROP ZONE STYLES */
          .root-drop-zone {
              min-height: 80px;
              margin: 10px 0;
              border: 3px dashed transparent;
              border-radius: 10px;
              transition: all 0.3s ease;
              display: none;
              background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
          }

          .edit-mode .root-drop-zone {
              display: block;
              border-color: #28a745;
          }

          .root-drop-zone.drop-ready {
              border-color: #28a745 !important;
              background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.2) 100%);
              transform: scale(1.02);
              box-shadow: 0 0 15px rgba(40, 167, 69, 0.4);
          }

          .root-drop-zone:hover.drop-ready {
              border-color: #1e7e34 !important;
              background: linear-gradient(135deg, rgba(40, 167, 69, 0.15) 0%, rgba(40, 167, 69, 0.25) 100%);
          }

          .root-drop-zone-content {
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
              height: 100%;
              color: #6c757d;
              padding: 15px;
              text-align: center;
          }

          .root-drop-zone-content .root-drop-zone-icon {
              font-size: 24px;
              margin-bottom: 5px;
          }

          .root-drop-zone-content .root-drop-zone-text {
              font-size: 14px;
              font-weight: bold;
              margin-bottom: 3px;
          }

          .root-drop-zone-content .root-drop-zone-help {
              font-size: 11px;
              opacity: 0.7;
              max-width: 250px;
          }

          .root-drop-zone.drop-ready .root-drop-zone-content {
              color: #28a745;
          }

          .dragging {
              opacity: 0.5;
              transform: rotate(2deg);
              z-index: 1000;
          }

          .sortable-ghost {
              opacity: 0.3;
              background-color: #f8f9fa;
          }

          .sortable-chosen {
              background-color: #e9ecef;
          }

          /* Enhanced visual feedback for drag and drop */
          body.edit-mode .folder-drop-zone.drop-ready,
          body.edit-mode .root-drop-zone.drop-ready {
              display: block !important;
              visibility: visible !important;
              animation: pulse 1.5s infinite;
          }
          
          @keyframes pulse {
              0% { box-shadow: 0 0 5px rgba(0, 123, 255, 0.3); }
              50% { box-shadow: 0 0 15px rgba(0, 123, 255, 0.6); }
              100% { box-shadow: 0 0 5px rgba(0, 123, 255, 0.3); }
          }

          /* Special pulse for root drop zone */
          .root-drop-zone.drop-ready {
              animation: root-pulse 1.5s infinite;
          }

          @keyframes root-pulse {
              0% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.3); }
              50% { box-shadow: 0 0 15px rgba(40, 167, 69, 0.6); }
              100% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.3); }
          }

          /* Make it clear when dragging that only drop zones are valid targets */
          .dragging ~ * ul:not(.folder-drop-zone):not(.root-drop-zone) {
              opacity: 0.3;
              pointer-events: none;
          }
          
          /* Cursor styling during drag with scroll hint */
          body.dragging-with-scroll {
              cursor: grabbing;
          }
          
          body.dragging-with-scroll * {
              cursor: grabbing !important;
          }
          
          /* Visual indicator for scroll availability during drag */
          .scroll-hint {
              position: fixed;
              top: 20px;
              right: 20px;
              background: rgba(0, 0, 0, 0.8);
              color: white;
              padding: 8px 12px;
              border-radius: 6px;
              font-size: 12px;
              z-index: 2000;
              pointer-events: none;
              opacity: 0;
              transition: opacity 0.2s ease;
          }
          
          .scroll-hint.show {
              opacity: 1;
          }
          `;
        document.head.appendChild(style);
    }
  }

});