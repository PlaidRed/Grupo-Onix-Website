$(document).ready(function() {
    
    // Initialize the dashboard
    initDashboard();
    
    /**
     * Initialize dashboard components
     */
    function initDashboard() {
        // Load users for the filter dropdown
        loadUsers();
        
        // Set default date range (last 30 days)
        setDefaultDateRange();
        
        // Initialize Select2 for user dropdown
        initSelect2();
        
        // Bind event handlers
        bindEventHandlers();
        
        // Load initial analytics data
        loadAnalytics();
        
        // Load recent clicks
        loadRecentClicks();
        
        // Initialize DataTables for clicks table
        initClicksTable();
    }
    
    /**
     * Load users from the server
     */
    function loadUsers() {
        $.ajax({
            url: 'include/Libs.php',
            type: 'POST',
            data: {
                action: 'getUsers'
            },
            dataType: 'json',
            beforeSend: function() {
                $('#fltUser').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Clear existing options (keep "Todos")
                    $('#fltUser option:not(:first)').remove();
                    
                    // Add users to dropdown
                    $.each(response.users, function(index, user) {
                        $('#fltUser').append(
                            $('<option>', {
                                value: user.id,
                                text: user.name
                            })
                        );
                    });
                    
                    console.log('Loaded ' + response.count + ' users');
                } else {
                    showNotification('Error al cargar usuarios: ' + response.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error de conexión al cargar usuarios', 'error');
                console.error('Error loading users:', error);
            },
            complete: function() {
                $('#fltUser').prop('disabled', false);
            }
        });
    }
    
    /**
     * Set default date range (last 30 days)
     */
    function setDefaultDateRange() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        $('#fltTo').val(formatDateForInput(today));
        $('#fltFrom').val(formatDateForInput(thirtyDaysAgo));
        
        // Highlight the 30d button
        $('.qrange[data-days="30"]').addClass('active');
    }
    
    /**
     * Initialize Select2 for better dropdown experience
     */
    function initSelect2() {
        $('#fltUser').select2({
            placeholder: 'Seleccionar usuario',
            allowClear: true,
            width: '100%'
        });
    }
    
    /**
     * Bind all event handlers
     */
    function bindEventHandlers() {
        // Apply filters button
        $('#btnApply').on('click', function() {
            loadAnalytics();
        });
        
        // Quick range buttons
        $('.qrange').on('click', function() {
            const days = parseInt($(this).data('days'));
            setDateRange(days);
            
            // Update active button
            $('.qrange').removeClass('active');
            $(this).addClass('active');
            
            // Auto-apply filters
            loadAnalytics();
        });
        
        // Enter key on date inputs
        $('#fltFrom, #fltTo').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                loadAnalytics();
            }
        });
        
        // User filter change
        $('#fltUser').on('change', function() {
            // Auto-apply when user changes (optional)
            // loadAnalytics();
        });
        
        // Refresh clicks button
        $('#refreshClicks').on('click', function() {
            loadRecentClicks();
        });
    }
    
    /**
     * Set date range based on days from today
     */
    function setDateRange(days) {
        const today = new Date();
        const startDate = new Date(today.getTime() - (days * 24 * 60 * 60 * 1000));
        
        $('#fltTo').val(formatDateForInput(today));
        $('#fltFrom').val(formatDateForInput(startDate));
    }
    
    /**
     * Load analytics data based on current filters
     */
    function loadAnalytics() {
        const filters = getFilters();
        
        // Validate date range
        if (!validateDateRange(filters.dateFrom, filters.dateTo)) {
            showNotification('La fecha "Desde" no puede ser mayor que la fecha "Hasta"', 'error');
            return;
        }
        
        // Show loading state
        showLoadingState();
        
        $.ajax({
            url: 'include/Libs.php',
            type: 'POST',
            data: {
                action: 'getAnalytics',
                userId: filters.userId,
                dateFrom: filters.dateFrom,
                dateTo: filters.dateTo
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAnalytics(response.data);
                } else {
                    showNotification('Error al cargar analíticas: ' + response.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error de conexión al cargar analíticas', 'error');
                console.error('Error loading analytics:', error);
            },
            complete: function() {
                hideLoadingState();
            }
        });
    }
    
    /**
     * Get current filter values
     */
    function getFilters() {
        return {
            userId: $('#fltUser').val() || '',
            dateFrom: $('#fltFrom').val(),
            dateTo: $('#fltTo').val()
        };
    }
    
    /**
     * Validate date range
     */
    function validateDateRange(dateFrom, dateTo) {
        if (!dateFrom || !dateTo) return true; // Allow empty dates
        
        const from = new Date(dateFrom);
        const to = new Date(dateTo);
        
        return from <= to;
    }
    
    /**
     * Display analytics data
     */
    function displayAnalytics(data) {
        console.log('Analytics data received:', data);
        
        // Update the existing card body content
        const cardBody = $('.card-body').last();
        
        if (!data) {
            cardBody.text('No hay datos disponibles para los filtros seleccionados.');
            return;
        }
        
        // Just update the text content for now
        cardBody.text('Datos cargados: ' + JSON.stringify(data));
    }
    
    /**
     * Show loading state
     */
    function showLoadingState() {
        $('#btnApply').prop('disabled', true).text('Cargando...');
        $('.card-body').last().text('Cargando datos de analíticas...');
    }
    
    /**
     * Hide loading state
     */
    function hideLoadingState() {
        $('#btnApply').prop('disabled', false).text('Aplicar');
    }
    
    /**
     * Show notification to user
     */
    function showNotification(message, type) {
        if (typeof bootbox !== 'undefined') {
            bootbox.alert(message);
        } else {
            alert(message);
        }
    }
    
    /**
     * Format date for input field (YYYY-MM-DD)
     */
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    /**
     * Format date for display (DD/MM/YYYY)
     */
    function formatDateForDisplay(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
    
    /**
     * Export current analytics data
     */
    function exportData(format) {
        const filters = getFilters();
        
        const form = $('<form>', {
            method: 'POST',
            action: 'include/Libs.php',
            target: '_blank'
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: 'exportData'
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'format',
            value: format || 'csv'
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'userId',
            value: filters.userId
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'dateFrom',
            value: filters.dateFrom
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'dateTo',
            value: filters.dateTo
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
    }
    
    /**
     * Load recent clicks data
     */
    function loadRecentClicks() {
        $.ajax({
            url: 'include/Libs.php',
            type: 'POST',
            data: {
                action: 'getRecentClicks',
                limit: 50
            },
            dataType: 'json',
            beforeSend: function() {
                $('#clicksTableBody').html('<tr><td colspan="5" class="text-center"><i class="la la-spinner la-spin"></i> Cargando datos...</td></tr>');
                $('#refreshClicks').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    displayRecentClicks(response.data);
                } else {
                    $('#clicksTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error: ' + response.error + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#clicksTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error de conexión</td></tr>');
                console.error('Error loading recent clicks:', error);
            },
            complete: function() {
                $('#refreshClicks').prop('disabled', false);
            }
        });
    }
    
    /**
     * Display recent clicks in the table
     */
    function displayRecentClicks(clicks) {
        const tbody = $('#clicksTableBody');
        
        if (!clicks || clicks.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay clicks registrados</td></tr>');
            return;
        }
        
        let html = '';
        
        clicks.forEach(function(click) {
            const date = new Date(click.created_at);
            const formattedDate = date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const formattedTime = date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            html += `
                <tr>
                    <td>
                        <div>${formattedDate}</div>
                        <small class="text-muted">${formattedTime}</small>
                    </td>
                    <td>${click.user_name || 'Usuario no identificado'}</td>
                    <td>
                        <strong>${click.cotizador_name}</strong>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" onclick="viewClickDetails(${click.id})" title="Ver detalles">
                            <i class="la la-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.html(html);
        
        // Refresh DataTable if it exists
        if ($.fn.DataTable.isDataTable('#clicksTable')) {
            $('#clicksTable').DataTable().destroy();
            initClicksTable();
        }
    }
    
    /**
     * Initialize DataTables for clicks table
     */
    function initClicksTable() {
        $('#clicksTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            columnDefs: [
                { orderable: false, targets: [4] }
            ]
        });
    }
    
    /**
     * View click details (to be implemented)
     */
    window.viewClickDetails = function(clickId) {
        // This function can be expanded to show more details in a modal
        console.log('View details for click ID:', clickId);
    };
    
    /**
     * Refresh dashboard data
     */
    function refreshDashboard() {
        loadUsers();
        loadAnalytics();
        loadRecentClicks();
    }
    
    // Make functions globally available if needed
    window.exportAnalytics = exportData;
    window.refreshDashboard = refreshDashboard;
    
});