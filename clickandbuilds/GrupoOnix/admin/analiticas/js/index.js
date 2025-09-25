$(document).ready(function () {

  // Initialize Select2 for better UX
  $('#fltUser').select2({
    placeholder: 'Seleccionar usuario',
    allowClear: true
  });

  // Load users on page load
  loadUsers();

  // Quick range buttons
  $('.qrange').on('click', function() {
    const days = $(this).data('days');
    const today = new Date();
    const fromDate = new Date();
    fromDate.setDate(today.getDate() - days);

    $('#fltTo').val(formatDate(today));
    $('#fltFrom').val(formatDate(fromDate));

    // Highlight active button
    $('.qrange').removeClass('active');
    $(this).addClass('active');
  });

  // Apply filters button
  $('#btnApply').on('click', function() {
    applyFilters();
  });

  /**
   * Load all users from sistema_usuario table
   */
  function loadUsers() {
    $.ajax({
      url: 'include/Libs.php',
      type: 'POST',
      data: {
        action: 'getUsers'
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          populateUserDropdown(response.data);
        } else {
          console.error('Error loading users:', response.error);
          bootbox.alert('Error al cargar usuarios: ' + response.error);
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading users:', error);
        console.error('Response:', xhr.responseText);
        bootbox.alert('Error de conexión al cargar usuarios');
      }
    });
  }

  /**
   * Populate user dropdown with data
   */
  function populateUserDropdown(users) {
    const $select = $('#fltUser');
    $select.empty();
    $select.append('<option value="">Todos</option>');

    users.forEach(function(user) {
      $select.append(
        $('<option></option>')
          .val(user.SIU_ID)
          .text(user.SIU_NOMBRE_COMPLETO)
      );
    });

    // Refresh Select2
    $select.trigger('change');
  }

  /**
   * Apply filters and load analytics data
   */
  function applyFilters() {
    const userId = $('#fltUser').val();
    const fromDate = $('#fltFrom').val();
    const toDate = $('#fltTo').val();

    console.log('Applying filters:', { userId, fromDate, toDate });

    $.ajax({
      url: 'include/Libs.php',
      type: 'POST',
      data: {
        action: 'getAnalytics',
        user_id: userId,
        from_date: fromDate,
        to_date: toDate
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          console.log('Analytics data:', response.data);
          displayAnalytics(response.data);
        } else {
          console.error('Error loading analytics:', response.error);
          bootbox.alert('Error al cargar analíticas: ' + response.error);
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading analytics:', error);
        console.error('Response:', xhr.responseText);
        bootbox.alert('Error de conexión al cargar analíticas');
      }
    });
  }

  /**
   * Display analytics data in a DataTable
   */
  function displayAnalytics(data) {
    // Get the card body specifically for analytics dashboard
    const $cardBody = $('.card-header:contains("Analíticas Dashboard")').next('.card-body');

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#analyticsTable')) {
      $('#analyticsTable').DataTable().destroy();
    }

    // Build table HTML
    let tableHTML = `
      <div class="alert alert-info">
        <strong>Total de clics:</strong> ${data.length}
      </div>
      <div class="table-responsive">
        <table id="analyticsTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Cotizador</th>
              <th>Acción</th>
              <th>Fecha y Hora</th>
              <th>User Agent</th>
            </tr>
          </thead>
          <tbody>
    `;

    if (data.length > 0) {
      data.forEach(function(row) {
        const fecha = formatDateTime(row.created_at);
        tableHTML += `
          <tr>
            <td>${row.id}</td>
            <td>${row.user_name || 'N/A'}</td>
            <td>${row.cotizador_name}</td>
            <td>${row.action_type}</td>
            <td>${fecha}</td>
            <td><small>${row.user_agent}</small></td>
          </tr>
        `;
      });
    } else {
      tableHTML += `
        <tr>
          <td colspan="6" class="text-center text-muted">No se encontraron registros</td>
        </tr>
      `;
    }

    tableHTML += `
          </tbody>
        </table>
      </div>
    `;

    // Update only the analytics dashboard card body
    $cardBody.html(tableHTML);

    // Initialize DataTable
    if (data.length > 0) {
      $('#analyticsTable').DataTable({
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
        },
        order: [[4, 'desc']], // Order by date descending
        pageLength: 25,
        responsive: true
      });
    }
  }

  /**
   * Format date time string
   */
  function formatDateTime(dateTimeStr) {
    if (!dateTimeStr) return 'N/A';
    const date = new Date(dateTimeStr);
    const options = { 
      year: 'numeric', 
      month: '2-digit', 
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    };
    return date.toLocaleString('es-MX', options);
  }

  /**
   * Format date to YYYY-MM-DD
   */
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

});