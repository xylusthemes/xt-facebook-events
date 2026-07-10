/**
 * HTB Live Feed - Admin JavaScript
 * Handles: tabs, copy shortcode, clear cache AJAX, live preview (no pagination)
 */
jQuery(function ($) {
  'use strict';

  var i18n = xtfeproFeedAdmin.i18n;

  // -------------------------------------------------------
  // Tabs
  // -------------------------------------------------------
  $('.xtfeprofeed-tabs-nav a').on('click', function (e) {
    e.preventDefault();
    var target = $(this).attr('href');
    $('.xtfeprofeed-tabs-nav a').removeClass('active');
    $(this).addClass('active');
    $('.xtfeprofeed-tab-content').removeClass('active');
    $(target).addClass('active');
  });

  // -------------------------------------------------------
  // Source type radio: show/hide relevant ID fields
  // -------------------------------------------------------
  function syncSourceRows() {
    var val = $('input[name="_xtfeprofeed_source_type"]:checked').val();
    $('.xtfeprofeed-source-row').hide();
    $('.xtfeprofeed-source-' + val).show();
  }

  $('input.xtfeprofeed-source-type-radio').on('change', syncSourceRows);
  syncSourceRows(); // init

  // -------------------------------------------------------
  // Time filter: show/hide custom date rows
  // -------------------------------------------------------
  function syncTimeRows() {
    var val = $('select[name="_xtfeprofeed_time_filter"]').val();
    $('.xtfeprofeed-time-row').hide();
    if (val === 'custom') {
      $('.xtfeprofeed-time-custom').show();
    }
  }

  $('select[name="_xtfeprofeed_time_filter"]').on('change', syncTimeRows);
  syncTimeRows(); // init

  // Datepicker init
  $('.xtfeprofeed-datepicker').datepicker({
    dateFormat: 'yy-mm-dd'
  });

  // -------------------------------------------------------
  // Layout picker: add active class on select
  // -------------------------------------------------------
  $(document).on('click', '.xtfeprofeed-layout-option.xtfeprofeed-layout-pro-only', function (e) {
    e.preventDefault();
    e.stopPropagation();
    return false;
  });

  $('.xtfeprofeed-layout-option input[type="radio"]').on('change', function () {
    $('.xtfeprofeed-layout-option').removeClass('active');
    $(this).closest('.xtfeprofeed-layout-option').addClass('active');
  });

  // -------------------------------------------------------
  // Copy shortcode (meta box sidebar)
  // -------------------------------------------------------
  $('#xtfeprofeed-copy-shortcode-btn').on('click', function () {
    var input = document.getElementById('xtfeprofeed-shortcode-input');
    input.select();
    input.setSelectionRange(0, 99999);
    try {
      document.execCommand('copy');
      $(this).text(i18n.copied).prop('disabled', true);
    } catch (e) {
      navigator.clipboard && navigator.clipboard.writeText(input.value);
      $(this).text(i18n.copied).prop('disabled', true);
    }
  });

  // Copy shortcode in list table (click code element)
  $(document).on('click', '.xtfeprofeed-copy-shortcode', function () {
    var sc   = $(this).data('shortcode');
    var $msg = $(this).next('.xtfeprofeed-copied');
    try {
      navigator.clipboard.writeText(sc).then(function () {
        $msg.fadeIn(200).delay(1500).fadeOut(400);
      });
    } catch (e) {
      var ta = document.createElement('textarea');
      ta.value = sc;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      $msg.fadeIn(200).delay(1500).fadeOut(400);
    }
  });

  // -------------------------------------------------------
  // Clear cache AJAX (meta box Cache tab)
  // -------------------------------------------------------
  $('#xtfeprofeed-clear-cache-btn').on('click', function () {
    var $btn = $(this);
    var $msg = $('#xtfeprofeed-clear-cache-msg');
    var feedId = $btn.data('feed-id');
    var nonce  = $btn.data('nonce');

    $btn.prop('disabled', true).text(i18n.clearing);
    $msg.hide().removeClass('success error');

    $.post(xtfeproFeedAdmin.ajax_url, {
      action:  'xtfeprofeed_clear_cache',
      feed_id: feedId,
      nonce:   nonce
    })
    .done(function (res) {
      if (res.success) {
        $msg.addClass('success').text(i18n.cache_cleared).show();
      } else {
        $msg.addClass('error').text(res.data.message || i18n.cache_error).show();
      }
    })
    .fail(function () {
      $msg.addClass('error').text(i18n.cache_error).show();
    })
    .always(function () {
      $btn.prop('disabled', false).text('Clear Cache Now');
    });
  });

  // -------------------------------------------------------
  // Clear hard cache AJAX (meta box Settings tab)
  // -------------------------------------------------------
  $('#xtfeprofeed-clear-hard-cache-btn').on('click', function () {
    var $btn = $(this);
    var $msg = $('#xtfeprofeed-clear-hard-cache-msg');
    var feedId = $btn.data('feed-id');
    var nonce  = $btn.data('nonce');

    if (!confirm('Are you sure you want to clear the hard cache? This will delete all cached event cover images.')) {
      return;
    }

    $btn.prop('disabled', true).text(i18n.clearing);
    $msg.hide().removeClass('success error');

    $.post(xtfeproFeedAdmin.ajax_url, {
      action:  'xtfeprofeed_clear_hard_cache',
      feed_id: feedId,
      nonce:   nonce
    })
    .done(function (res) {
      if (res.success) {
        $msg.addClass('success').text(i18n.hard_cleared || 'Hard cache cleared!').show();
        // Update count text if present
        $btn.siblings('.description').first().html('<span style="color:#aaa;">&#9679; No HQ images cached yet.</span>');
      } else {
        $msg.addClass('error').text(res.data.message || i18n.cache_error).show();
      }
    })
    .fail(function () {
      $msg.addClass('error').text(i18n.cache_error).show();
    })
    .always(function () {
      $btn.prop('disabled', false).text('🗑 Clear Hard Cache (Images)');
    });
  });

  // -------------------------------------------------------
  // Cache duration: show/hide custom minutes input
  // -------------------------------------------------------
  $('input.xtfeprofeed-cache-preset').on('change', function () {
    var val = $(this).val();
    if (val === 'custom') {
      $('.xtfeprofeed-cache-custom-wrap').show().find('input').focus();
    } else {
      $('.xtfeprofeed-cache-custom-wrap').hide();
    }
  });

  // -------------------------------------------------------
  // Live Preview Update (layout sample only — no pagination)
  // -------------------------------------------------------
  var previewTimeout = null;

  function updateLivePreview() {
    var $container1 = $('#xtfeprofeed-preview-container');
    var $container2 = $('#xtfepro-builder-preview-container');
    var $containers = $container1.add($container2);

    if (!$containers.length) {
      return;
    }

    var isFullPreview = $('.xtfepro-builder__workspace').hasClass('is-full-preview');
    var $loading = $('.xtfeprofeed-preview-loading');

    $loading.show();

    var formData = $('form#post').serializeArray();
    var feedId = $('#post_ID').val() || 0;

    formData.push({ name: 'action', value: 'xtfeprofeed_live_preview' });
    formData.push({ name: 'feed_id', value: feedId });
    formData.push({ name: 'is_full_preview', value: isFullPreview ? 'true' : 'false' });

    $.post(xtfeproFeedAdmin.ajax_url, formData)
      .done(function (res) {
        var $globalWarning = $('#xtfepro-builder-global-warning');
        
        if (res.success && res.data.html) {
          $containers.html(res.data.html);
          $globalWarning.hide();

          $containers.find('img').each(function () {
            if (this.complete) {
              this.style.opacity = 1;
              var prev = this.previousElementSibling;
              if (prev && prev.classList.contains('xtfeprofeed-skeleton')) {
                prev.style.display = 'none';
              }
            }
          });
        } else {
          var errorMsg = res.data.message || 'Error loading preview';
          var errHtml = '<div class="xtfeprofeed-preview-error"><p>' + errorMsg + '</p></div>';
          $containers.html(errHtml);
          
          if (errorMsg.indexOf("This content isn't available") !== -1 || errorMsg.indexOf("private") !== -1 || errorMsg.indexOf("restricted") !== -1) {
            var sourceType = $('input[name="_xtfeprofeed_source_type"]:checked').val();
            var warningText = 'We cannot fetch data because the Facebook Page/Event might be private or country-restricted. Please change the Page ID or check settings.';
            if (sourceType === 'group_id') {
              warningText = 'We cannot fetch data because the Facebook Group might be private or country-restricted. Please change the Group ID or check settings.';
            }
            $globalWarning.find('.xtfepro-warning-text').text(warningText);
            $globalWarning.show();
          } else {
            $globalWarning.hide();
          }
        }
      })
      .fail(function () {
        var failHtml = '<div class="xtfeprofeed-preview-error"><p>Failed to contact server for preview.</p></div>';
        $containers.html(failHtml);
      })
      .always(function () {
        $loading.hide();
      });
  }

  function triggerPreviewUpdate() {
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(updateLivePreview, 400);
  }

  $(document).on('change input click',
    '#xtfeprofeed-tab-source input, #xtfeprofeed-tab-source select, ' +
    '#xtfepro-panel-body-source input, #xtfepro-panel-body-source select, ' +
    '#xtfeprofeed-tab-display input, #xtfeprofeed-tab-display select, ' +
    '#xtfeprofeed-tab-tickets input, #xtfeprofeed-tab-tickets select, ' +
    '#xtfeprofeed-tab-filters input, #xtfeprofeed-tab-filters select, ' +
    '#xtfepro-panel-body-display input, #xtfepro-panel-body-display select, ' +
    '#xtfepro-panel-body-tickets input, #xtfepro-panel-body-tickets select, ' +
    '#xtfepro-panel-body-filters input, #xtfepro-panel-body-filters select, ' +
    '.xtfeprofeed-layout-option:not(.xtfeprofeed-layout-pro-only)',
    triggerPreviewUpdate
  );

  setTimeout(updateLivePreview, 500);

  // -------------------------------------------------------
  // Toggle Full Preview mode
  // -------------------------------------------------------
  $(document).on('click', '#xtfepro-builder-toggle-full-preview', function (e) {
    e.preventDefault();
    var $workspace = $('.xtfepro-builder__workspace');
    var $builder = $('#xtfepro-builder');
    var $btn = $(this);
    var $icon = $btn.find('.dashicons');
    var $text = $btn.find('.btn-text');

    if ($workspace.hasClass('is-full-preview')) {
      $workspace.removeClass('is-full-preview');
      $builder.removeClass('is-full-preview');
      $icon.removeClass('dashicons-editor-contract').addClass('dashicons-editor-expand');
      $text.text('Full Preview');
    } else {
      $workspace.addClass('is-full-preview');
      $builder.addClass('is-full-preview');
      $icon.removeClass('dashicons-editor-expand').addClass('dashicons-editor-contract');
      $text.text('Close Preview');
    }

    updateLivePreview();
  });
});
