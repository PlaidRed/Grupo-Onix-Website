document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',

        // Traducción de botones y textos
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        allDayText: 'Todo el día',

        // Header nativo de FullCalendar
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },

        // Fuentes de eventos
        eventSources: [
            {
                url: 'memos/include/Libs.php?accion=getMemosForCalendar',
                method: 'GET',
                failure: function () {
                    alert('Error cargando eventos');
                },
                success: function(data) {
                    return data.events.map(event => {
                        event.url = `avisos/aviso.php?id=${event.id}`;
                        
                        // Debugging to see what colors are being received
                        console.log('Event:', event.title, 'Background:', event.backgroundColor, 'Border:', event.borderColor);

                        return event;
                    });
                },
                extraParams: { _ts: new Date().getTime() }
            },
            {
                url: 'cotizadores/abc/include/Libs.php?accion=getPasswordChanges',
                method: 'GET',
                failure: function () { console.error('Error cargando cambios de contraseñas'); },
                success: function(data) {
                    return data.events.map(event => {
                        event.url = 'cotizadores/asegurador/index.php';

                        // Keep the explicit color setting for password changes
                        event.backgroundColor = '#4CAF50';
                        event.borderColor = '#4CAF50';
                        return event;
                    });
                },
                extraParams: { _ts: new Date().getTime() }
            }
        ],

        // Click en eventos
        eventClick: function (info) {
            if(info.event.url){
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        },

        // Reinyectar leyenda al cambiar de vista/mes (sin duplicar)
        datesSet: function() {
            injectLegend();
        },

        
    });

    calendar.render();
    injectLegend(); // primera inyección tras el render

    // ---- Inserta leyenda a los lados del título dentro del chunk central ----
    function injectLegend() {
        const centerChunk = document.querySelector('#calendar .fc-header-toolbar .fc-toolbar-chunk:nth-child(2)');
        if (!centerChunk) return;

        // evitar duplicados de la leyenda
        if (centerChunk.querySelector('.fc-title-legend')) return;

        const titleEl = centerChunk.querySelector('.fc-toolbar-title');
        if (!titleEl) return;

        const titleNode = titleEl;
        centerChunk.innerHTML = '';

        const container = document.createElement('div');
        container.className = 'fc-title-legend';

        const left = buildLegendCol([
            ['Circular',        '#9c27b0'],
            ['Avisos temp.',    '#ff9800'],
            ['Contraseñas',     '#4caf50'],
            ['Asuetos',       '#ff0090ff']

        ]);

        const right = buildLegendCol([
            ['Vacaciones',      '#2196f3'],
            ['Importantes',     '#f44336'],
            ['Otros',           '#9e9e9e']
        ]);

        container.appendChild(left);
        container.appendChild(titleNode);
        container.appendChild(right);

        centerChunk.appendChild(container);
    }

    function buildLegendCol(items) {
        const col = document.createElement('div');
        col.className = 'legend-col';
        col.style.display = 'flex';
        col.style.gap = '10px';
        items.forEach(([label, color]) => {
            const item = document.createElement('div');
            item.className = 'legend-item';
            item.style.display = 'flex';
            item.style.alignItems = 'center';
            item.style.gap = '4px';

            const dot = document.createElement('span');
            dot.className = 'legend-dot';
            dot.style.display = 'inline-block';
            dot.style.width = '10px';
            dot.style.height = '10px';
            dot.style.borderRadius = '50%';
            dot.style.backgroundColor = color;

            const text = document.createElement('span');
            text.textContent = label;

            item.appendChild(dot);
            item.appendChild(text);
            col.appendChild(item);
        });
        return col;
    }

});
