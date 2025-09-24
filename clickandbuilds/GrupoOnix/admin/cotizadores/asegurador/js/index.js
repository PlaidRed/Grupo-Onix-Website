$(document).ready(function () {

  //buscador y boton de borrar
  $('#search').on('input', function () {
    const query = $(this).val().toLowerCase();

    $('.aseguradora-card').each(function () {
      const name = $(this).find('.aseguradora-nombre').text().toLowerCase();

      if (name.includes(query)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  $('#clear-search').on('click', function () {
    $('#search').val(''); // clear input
    $('.aseguradora-card').show(); // show all cards again
    $(this).blur(); // remove focus from button
  });

  // Botones copiar y pegar para usuario y contraseña
  $('.copy-btn').on('click', function () {
    const targetId = $(this).data('target');
    const valueToCopy = $('#' + targetId).val();

    if (!valueToCopy) return;

    // Create a hidden textarea to copy from (prevents showing password)
    const $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(valueToCopy).select();

    try {
      document.execCommand('copy');
      console.log('Copied: ' + valueToCopy);
      showTooltip($(this), '¡Copiado!');
    } catch (err) {
      console.error('Copy failed', err);
      showTooltip($(this), 'Error');
    }

    $temp.remove();
    $(this).blur();
  });

  function showTooltip($button, message) {
    const $tooltip = $('<span class="copy-tooltip"></span>').text(message);
    $button.append($tooltip);
    $tooltip.fadeIn(150).delay(800).fadeOut(300, function () {
      $(this).remove();
    });
  }

  // ANALYTICS TRACKING - Track "Ir a Página" clicks
  $('.ir-pagina-link').on('click', function(e) {
    const $link = $(this);
    const cotizadorName = $link.closest('.aseguradora-card').find('.aseguradora-nombre').text();
    const trackingData = $link.data('track');
    
    // Send analytics data
    trackClick(cotizadorName);
    
  });

  /**
   * Track click events and send to analytics
   */
  function trackClick(cotizadorName) {
    $.ajax({
      url: '../../analiticas/include/Libs.php',
      type: 'POST',
      data: {
        action: 'trackClick',
        cotizador_name: cotizadorName,
        user_agent: navigator.userAgent,
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          console.log('Click tracked successfully:', cotizadorName);
        } else {
          console.error('Error tracking click:', response.error);
        }
      },
      error: function(xhr, status, error) {
        console.error('Analytics tracking failed:', error);
      }
    });
  }

});