$(document).ready(function(){

    // Map category names → hex colors (must match PHP getMemosForCalendar)
    var colorMap = {
        'Circular': '#9C27B0',
        'Avisos temporales': '#FF9800',
        'Vacaciones': '#2196F3',
        'Contrasenas': '#4CAF50',
        'Importantes': '#F44336',
        'Asuetos': '#FF0090FF',
        'Otros': '#9E9E9E'
    };

    // Update preview color when selection changes
    $('#color').on('change', function() {
        var selected = $(this).val();
        var hex = colorMap[selected] || '#9E9E9E'; // fallback gray
        $('#color-preview').css('background-color', hex);
    });

    var selectedBaseDates = [];

    // Trigger change to set initial preview
    $('#color').trigger('change');

    $('#mensaje').summernote({
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
        tabsize: 2,
        height: 300
    });

    // Initialize datepickers
    $("#fecha, #fecha_exp").datepicker({
        dateFormat: 'dd/mm/yy'
    });

    $("#repeat-until").datepicker({
        dateFormat: 'dd/mm/yy'
    });

    // Show/hide repetitive calendar
    $('#repetitivo').on('change', function() {
        if ($(this).is(':checked')) {
            $('#repeat-calendar-container').show();
            $('#fecha, #fecha_exp').prop('disabled', true).addClass('disabled-input');
            $('#hora_inicial, #hora_exp').prop('disabled', false).removeClass('disabled-input');

            
            // Initialize the permanent calendar widget if not already done
            if (!$('#repeat-calendar-widget').hasClass('hasDatepicker')) {
                initializePermanentCalendar();
            }
        } else {
            $('#repeat-calendar-container').hide();
            $('#fecha, #fecha_exp').prop('disabled', false).removeClass('disabled-input');
            
            // Clear all repetitive data
            selectedBaseDates = [];
            $('#repeat_dates').val('');
            $('#repeat_config').val('');
            updateSelectedDatesList();
        }
    });

    function initializePermanentCalendar() {
        $('#repeat-calendar-widget').datepicker({
            dateFormat: 'dd/mm/yy',
            showButtonPanel: true,
            beforeShowDay: function(date) {
                var dateStr = $.datepicker.formatDate('dd/mm/yy', date);
                return [true, selectedBaseDates.includes(dateStr) ? 'ui-state-highlight' : ''];
            },
            onSelect: function(dateText, inst) {
                var index = selectedBaseDates.indexOf(dateText);
                if (index > -1) {
                    // Remove date
                    selectedBaseDates.splice(index, 1);
                } else {
                    // Add date
                    selectedBaseDates.push(dateText);
                }
                
                // Sort dates
                selectedBaseDates.sort(function(a, b) {
                    var partsA = a.split('/'), partsB = b.split('/');
                    return new Date(partsA[2], partsA[1]-1, partsA[0]) - new Date(partsB[2], partsB[1]-1, partsB[0]);
                });
                
                updateSelectedDatesList();
                updateHiddenFields();
                $(this).datepicker('refresh');
            }
        });
    }

    function updateSelectedDatesList() {
        var $container = $('#selected-dates-list');
        
        if (selectedBaseDates.length === 0) {
            $container.html('<em class="text-muted">No hay fechas seleccionadas</em>');
            $('#selected-dates-config').hide();
        } else {
            var html = '';
            selectedBaseDates.forEach(function(date) {
                html += '<span class="selected-date-tag">' + 
                       date + 
                       '<span class="remove-date" data-date="' + date + '">×</span>' +
                       '</span>';
            });
            $container.html(html);
            $('#selected-dates-config').show();
        }
    }

    function updateHiddenFields() {
        // Update the hidden field that the backend expects
        $('#repeat_dates').val(selectedBaseDates.join(','));
    }

    // Remove individual dates
    $(document).on('click', '.remove-date', function() {
        var dateToRemove = $(this).data('date');
        var index = selectedBaseDates.indexOf(dateToRemove);
        if (index > -1) {
            selectedBaseDates.splice(index, 1);
            updateSelectedDatesList();
            updateHiddenFields();
            $('#repeat-calendar-widget').datepicker('refresh');
        }
    });

    // Handle repeat pattern changes
    $('#repeat-pattern').on('change', function() {
        var pattern = $(this).val();
        
        if (pattern === 'none') {
            $('#repeat-frequency-group, #repeat-count-group, #repeat-until-group').hide();
        } else {
            $('#repeat-frequency-group, #repeat-count-group, #repeat-until-group').show();
            
            // Update frequency unit text
            var unitText = {
                'daily': 'días',
                'weekly': 'semanas',
                'monthly': 'meses',
                'yearly': 'años'
            };
            $('#frequency-unit').text(unitText[pattern] || 'días');
        }
    });

    // Preview dates functionality
    $('#preview-dates').on('click', function() {
        if (selectedBaseDates.length === 0) {
            alert('Por favor seleccione al menos una fecha base.');
            return;
        }

        var pattern = $('#repeat-pattern').val();
        var frequency = parseInt($('#repeat-frequency').val()) || 1;
        var count = parseInt($('#repeat-count').val()) || 1;
        var until = $('#repeat-until').val();
        
        var generatedDates = generateRepetitiveDates(selectedBaseDates, pattern, frequency, count, until);
        displayPreview(generatedDates);
    });

    function generateRepetitiveDates(baseDates, pattern, frequency, count, until) {
        var allDates = [];
        var maxPreviewDates = 50; // Limit preview
        
        // Add base dates
        baseDates.forEach(function(dateStr) {
            allDates.push({
                date: dateStr,
                type: 'base'
            });
        });

        if (pattern !== 'none') {
            var untilDate = until ? parseDate(until) : null;
            
            baseDates.forEach(function(baseDateStr) {
                var baseDate = parseDate(baseDateStr);
                
                for (var i = 1; i <= count && allDates.length < maxPreviewDates; i++) {
                    var newDate = new Date(baseDate);
                    
                    switch (pattern) {
                        case 'daily':
                            newDate.setDate(baseDate.getDate() + (i * frequency));
                            break;
                        case 'weekly':
                            newDate.setDate(baseDate.getDate() + (i * frequency * 7));
                            break;
                        case 'monthly':
                            newDate.setMonth(baseDate.getMonth() + (i * frequency));
                            break;
                        case 'yearly':
                            newDate.setFullYear(baseDate.getFullYear() + (i * frequency));
                            break;
                    }
                    
                    // Check if we've exceeded the until date
                    if (untilDate && newDate > untilDate) {
                        break;
                    }
                    
                    allDates.push({
                        date: formatDate(newDate),
                        type: 'repeated'
                    });
                }
            });
        }

        // Sort all dates
        allDates.sort(function(a, b) {
            return parseDate(a.date) - parseDate(b.date);
        });

        return allDates.slice(0, maxPreviewDates);
    }

    function displayPreview(dates) {
        var $container = $('#preview-dates-list');
        var html = '';
        var horaInicial = $('#hora_inicial').val();
        var horaExp = $('#hora_exp').val();
        var timeDisplay = '';
        
        if (horaInicial || horaExp) {
            timeDisplay = ' (' + (horaInicial || '00:00') + 
                        (horaExp && horaExp !== horaInicial ? ' - ' + horaExp : '') + ')';
        }
        
        dates.forEach(function(dateObj) {
            var badge = dateObj.type === 'base' ? 
                '<span class="badge badge-primary">Base</span>' : 
                '<span class="badge badge-secondary">Repetida</span>';
            html += '<div class="preview-date-item">' + dateObj.date + timeDisplay + ' ' + badge + '</div>';
        });
        
        $container.html(html);
        $('#preview-results').show();
    }

    function parseDate(dateStr) {
        var parts = dateStr.split('/');
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    function formatDate(date) {
        var day = String(date.getDate()).padStart(2, '0');
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var year = date.getFullYear();
        return day + '/' + month + '/' + year;
    }

    // Clear all repetitive configuration
    $('#clear-repeat-config').on('click', function() {
        selectedBaseDates = [];
        $('#repeat-pattern').val('none').trigger('change');
        $('#repeat-frequency').val(1);
        $('#repeat-count').val(1);
        $('#repeat-until').val('');
        $('#preview-results').hide();
        updateSelectedDatesList();
        updateHiddenFields();
        if ($('#repeat-calendar-widget').hasClass('hasDatepicker')) {
            $('#repeat-calendar-widget').datepicker('refresh');
        }
    });

    // Helper function to convert date and time to datetime string
    function convertToDateTime(dateStr, timeStr) {
        if (!dateStr) return null;
        
        var dateParts = dateStr.split('/');
        var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
        var time = timeStr || '00:00';
        return new Date(formattedDate + ' ' + time + ':00');
    }

    // Enhanced validation function
    function validateDateTime() {
        var repetitivoChecked = $('#repetitivo').is(':checked');
        
        if (repetitivoChecked) {
            if (selectedBaseDates.length === 0) {
                return { 
                    valid: false, 
                    message: 'Por favor seleccione al menos una fecha base para la repetición.', 
                    focus: 'repeat-calendar-widget' 
                };
            }
            
            var pattern = $('#repeat-pattern').val();
            if (pattern !== 'none') {
                var count = parseInt($('#repeat-count').val()) || 1;
                var until = $('#repeat-until').val();
                
                if (!until && count < 1) {
                    return { 
                        valid: false, 
                        message: 'Debe especificar un número de repeticiones válido o una fecha límite.', 
                        focus: 'repeat-count' 
                    };
                }
                
                if (until) {
                    var untilDate = parseDate(until);
                    var earliestBase = parseDate(selectedBaseDates[0]);
                    if (untilDate <= earliestBase) {
                        return { 
                            valid: false, 
                            message: 'La fecha límite debe ser posterior a las fechas base seleccionadas.', 
                            focus: 'repeat-until' 
                        };
                    }
                }
            }
        } else {
            // Existing validation for regular dates
            var fecha = $('#fecha').val();
            if (!fecha) {
                return { 
                    valid: false, 
                    message: 'La fecha inicial es requerida.', 
                    focus: 'fecha' 
                };
            }
            
            var fechaExp = $('#fecha_exp').val();
            var horaInicial = $('#hora_inicial').val();
            var horaExp = $('#hora_exp').val();
            
            if (fechaExp && fecha) {
                var startDateTime = convertToDateTime(fecha, horaInicial);
                var endDateTime = convertToDateTime(fechaExp, horaExp);

                if (endDateTime <= startDateTime) {
                    return { 
                        valid: false, 
                        message: 'La fecha de expiración debe ser posterior a la fecha inicial.', 
                        focus: 'fecha_exp' 
                    };
                }
            }
        }

        return { valid: true };
    }

    // Load existing record for editing
    function getRecord() {
        var id = $("#id").val();
        if (!id || id === '-1') return;

        $.ajax({
            type: 'POST',
            url: 'include/Libs.php?accion=showRecord',
            data: { id: id },
            dataType: 'json',
            success: function(result) {
                if (result.error) {
                    window.location = "index.php";
                    return;
                }

                // Populate basic fields
                if (result.titulo) $("#titulo").val(result.titulo);
                if (result.contenido) $('#mensaje').summernote('code', result.contenido);
                if (result.color) $("#color").val(result.color).trigger('change');
                if (result.pdf) $("#pdf").after(result.pdf);

                // Handle dates - check if this is a repetitive event
                if (result.repetitivo_fechas && result.repetitivo_fechas.length > 0) {
                    // This is a repetitive event - existing logic is fine
                    $('#repetitivo').prop('checked', true);
                    $('#repeat-calendar-container').show();
                    $('#fecha, #fecha_exp').prop('disabled', true).addClass('disabled-input');
                    $('#hora_inicial, #hora_exp').prop('disabled', false).removeClass('disabled-input');                    
                    
                    // Parse the repetitive dates from JSON
                    var dates = JSON.parse(result.repetitivo_fechas);
                    
                    // Convert YYYY-MM-DD to DD/MM/YYYY format and extract unique dates
                    var uniqueDates = [];
                    dates.forEach(function(dateStr) {
                        var datePart = dateStr.split(' ')[0]; // Remove time part
                        var parts = datePart.split('-');
                        var formattedDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                        if (!uniqueDates.includes(formattedDate)) {
                            uniqueDates.push(formattedDate);
                        }
                    });
                    
                    selectedBaseDates = uniqueDates;
                    
                    // Sort dates
                    selectedBaseDates.sort(function(a, b) {
                        var partsA = a.split('/'), partsB = b.split('/');
                        return new Date(partsA[2], partsA[1]-1, partsA[0]) - new Date(partsB[2], partsB[1]-1, partsB[0]);
                    });
                    
                    // Handle times for repetitive events
                    if (dates.length > 0 && dates[0].includes(' ')) {
                        var firstDateTime = dates[0];
                        var timePart = firstDateTime.split(' ')[1];
                        if (timePart && timePart !== '00:00:00') {
                            $('#hora_inicial').val(timePart.substring(0, 5));
                        }
                        
                        // Check if there's an end time
                        var firstDate = dates[0].split(' ')[0];
                        var endTimeEntry = dates.find(function(d) {
                            return d.startsWith(firstDate) && d !== dates[0];
                        });
                        
                        if (endTimeEntry) {
                            var endTime = endTimeEntry.split(' ')[1];
                            if (endTime) {
                                $('#hora_exp').val(endTime.substring(0, 5));
                            }
                        }
                    }
                    
                    // Load repeat configuration if available
                    if (result.repeat_config) {
                        try {
                            var config = JSON.parse(result.repeat_config);
                            if (config.pattern) $('#repeat-pattern').val(config.pattern).trigger('change');
                            if (config.frequency) $('#repeat-frequency').val(config.frequency);
                            if (config.count) $('#repeat-count').val(config.count);
                            if (config.until) $('#repeat-until').val(config.until);
                        } catch(e) {
                            console.log('Could not parse repeat_config:', e);
                        }
                    }
                    
                    // Update UI
                    updateSelectedDatesList();
                    updateHiddenFields();
                    
                    // Refresh calendar widget after a short delay
                    setTimeout(function() {
                        if ($('#repeat-calendar-widget').hasClass('hasDatepicker')) {
                            $('#repeat-calendar-widget').datepicker('refresh');
                        }
                    }, 100);
                    
                } else {
                    // This is a single event - FIXED LOGIC
                    $('#repetitivo').prop('checked', false);
                    $('#repeat-calendar-container').hide();
                    $('#fecha, #fecha_exp').prop('disabled', false).removeClass('disabled-input');
                    $('#hora_inicial, #hora_exp').prop('disabled', false).removeClass('disabled-input');
                    
                    // Handle start date/time - FIXED
                    if (result.fecha && result.fecha !== '0000-00-00 00:00:00') {
                        console.log('Loading fecha:', result.fecha);
                        
                        // The result.fecha comes from showRecord() which formats it as "d/m/Y H:i"
                        // So we need to parse it differently than the database format
                        var fechaParts = result.fecha.split(' ');
                        var datePart = fechaParts[0]; // Should be in DD/MM/YYYY format already
                        var timePart = fechaParts[1] || '';
                        
                        console.log('Date part:', datePart, 'Time part:', timePart);
                        
                        // The date should already be in DD/MM/YYYY format from showRecord()
                        if (datePart && datePart.includes('/')) {
                            $("#fecha").val(datePart);
                            console.log('Set fecha to:', datePart);
                        }
                        
                        // Set time if provided and not 00:00
                        if (timePart && timePart !== '00:00') {
                            $("#hora_inicial").val(timePart);
                            console.log('Set hora_inicial to:', timePart);
                        }
                    }
                    
                    // Handle end date/time
                    if (result.fechaExp && result.fechaExp !== '0000-00-00 00:00:00') {
                        console.log('Loading fechaExp:', result.fechaExp);
                        
                        // Same logic - fechaExp comes formatted from showRecord()
                        var fechaExpParts = result.fechaExp.split(' ');
                        var datePart = fechaExpParts[0]; // Should be in DD/MM/YYYY format already
                        var timePart = fechaExpParts[1] || '';
                        
                        console.log('FechaExp date part:', datePart, 'Time part:', timePart);
                        
                        // The date should already be in DD/MM/YYYY format from showRecord()
                        if (datePart && datePart.includes('/')) {
                            $("#fecha_exp").val(datePart);
                            console.log('Set fecha_exp to:', datePart);
                        }
                        
                        // FIXED: Set time if provided - removed the restrictive condition
                        if (timePart && timePart.length >= 5) {
                            // Extract HH:MM from the time part (in case it includes seconds)
                            var timeFormatted = timePart.substring(0, 5);
                            $("#hora_exp").val(timeFormatted);
                            console.log('Set hora_exp to:', timeFormatted);
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('getRecord Error:', status, error, xhr.responseText);
                bootbox.dialog({
                    message: "Error técnico al cargar los datos.",
                    buttons: {
                        cerrar: {
                            label: "Cerrar",
                            callback: function() {
                                bootbox.hideAll();
                            }
                        }
                    }
                });
            }
        });
    }

    // SINGLE form submission handler
    $(document).on('submit', '#frm-marca', function(e) {
        e.preventDefault();
        
        // Validate datetime before submitting
        var validation = validateDateTime();
        if (!validation.valid) {
            bootbox.dialog({
                message: validation.message,
                buttons: {
                    cerrar: {
                        label: "Cerrar",
                        callback: function() {
                            bootbox.hideAll();
                            $('#' + validation.focus).focus();
                        }
                    }
                }
            });
            return;
        }

        // Create FormData
        var formdata = new FormData($('form[id="frm-marca"]')[0]);

        // Add category label
        var selectedCategory = $('#color').val();
        formdata.set("color", selectedCategory);

        var repetitivoChecked = $('#repetitivo').is(':checked');

        if (repetitivoChecked) {
            // Generate final repetitive dates
            var pattern = $('#repeat-pattern').val();
            var frequency = parseInt($('#repeat-frequency').val()) || 1;
            var count = parseInt($('#repeat-count').val()) || 1;
            var until = $('#repeat-until').val();
            
            var finalDates = generateRepetitiveDates(selectedBaseDates, pattern, frequency, count, until);
            
            // Convert to MySQL DATETIME format as simple string array (matching existing DB structure)
            var repetitiveDateTimes = [];
            var horaInicial = $('#hora_inicial').val() || '00:00';
            var horaExp = $('#hora_exp').val();

            finalDates.forEach(function(dateObj) {
                var parts = dateObj.date.split('/');
                var baseDateTime = parts[2] + '-' + parts[1] + '-' + parts[0];
                
                // Add start time entry
                repetitiveDateTimes.push(baseDateTime + ' ' + horaInicial + ':00');
                
                // Add end time entry if different from start time
                if (horaExp && horaExp !== horaInicial) {
                    repetitiveDateTimes.push(baseDateTime + ' ' + horaExp + ':00');
                }
            });

            // Store configuration for future reference
            var config = {
                baseDates: selectedBaseDates,
                pattern: pattern,
                frequency: frequency,
                count: count,
                until: until,
                generatedCount: repetitiveDateTimes.length
            };

            formdata.set('repetitivo_fechas', JSON.stringify(repetitiveDateTimes));
            formdata.set('repeat_config', JSON.stringify(config));
            
            // Remove regular date fields
            formdata.delete('fecha_datetime');
            formdata.delete('fecha_exp_datetime');
        } else {
            // Handle regular dates
            formdata.delete('repetitivo_fechas');
            formdata.delete('repeat_config');

            var fecha = $('#fecha').val();
            var fechaExp = $('#fecha_exp').val();
            var horaInicial = $('#hora_inicial').val();
            var horaExp = $('#hora_exp').val();

            var timeInicial = horaInicial || '00:00';
            var timeExp = horaExp || '23:59';

            if (fecha) {
                var dateParts = fecha.split('/');
                var fechaDatetime = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + ' ' + timeInicial + ':00';
                formdata.set('fecha_datetime', fechaDatetime);
            }

            if (fechaExp) {
                var expParts = fechaExp.split('/');
                var fechaExpDatetime = expParts[2] + '-' + expParts[1] + '-' + expParts[0] + ' ' + timeExp + ':00';
                formdata.set('fecha_exp_datetime', fechaExpDatetime);
            }
        }

        // Determine if all-day event
        var isAllDay = !$('#hora_inicial').val();
        formdata.set('todo_el_dia', isAllDay ? '1' : '0');

        // Submit form
        $.ajax({
            type: 'POST',
            url: 'include/Libs.php?accion=saveRecord',
            data: formdata,
            processData: false,
            contentType: false,
            dataType:'json',
            beforeSend: function(){
                $('input, file, textarea, button, select').attr('disabled','disabled');
            },
            error: function(){
                $('input, file, textarea, button, select').removeAttr('disabled');
                bootbox.dialog({
                    message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
                    buttons: { cerrar: { label: "Cerrar", callback: function(){ bootbox.hideAll(); } } }
                });
            },
            success: function(result){
                $('input, file, textarea, button, select').removeAttr('disabled');
                bootbox.dialog({
                    message: result.msg,
                    buttons: {
                        cerrar: {
                            label: "Cerrar",
                            callback: function() {
                                if(result.error) {
                                    bootbox.hideAll();
                                    $('#' + result.focus).focus();
                                } else {
                                    window.location = "index.php";
                                }
                            }
                        }
                    }
                });
            }
        });
    });

    // Guardar button click handler
    $(document).on('click','.guardar',function(e) {
        e.preventDefault();
        $('#frm-marca').submit();
    });

    // Load the record when page loads
    getRecord();
});