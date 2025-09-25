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

  // Track "Ir a Página" clicks
  $('.ir-pagina-link').on('click', function(e) {
    const $link = $(this);
    const cotizadorName = $link.data('cotizador-name');
    const cotizadorId = $link.data('cotizador-id');
    
    console.log('Tracking click for:', cotizadorName);
    
    // Send tracking data (non-blocking)
    trackClick(cotizadorName, cotizadorId);
  });

  /**
   * Track click events and send to backend
   */
  function trackClick(cotizadorName, cotizadorId) {
    $.ajax({
      url: 'include/track.php',
      type: 'POST',
      data: {
        action: 'trackClick',
        cotizador_name: cotizadorName,
        cotizador_id: cotizadorId,
        user_agent: navigator.userAgent
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          console.log('✓ Click tracked successfully:', response.data);
        } else {
          console.error('✗ Tracking error:', response.error);
        }
      },
      error: function(xhr, status, error) {
        console.error('✗ AJAX failed:', error);
        console.error('Response:', xhr.responseText);
      }
    });
  }

});