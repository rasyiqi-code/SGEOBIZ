// Modern Dashboard Layout Transformation
jQuery(document).ready(function($) {
    if (!$('.sgeobiz-metaboxes').length) return;

    // Helper to read query parameter
    var getUrlParameter = function(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };

    var currentSection = getUrlParameter('section') || 'general';

    // Map section slug to metabox element ID
    var sectionMap = {
        'general': '#autodescription-general-settings',
        'title': '#autodescription-title-settings',
        'description': '#autodescription-description-settings',
        'social': '#autodescription-social-settings',
        'homepage': '#autodescription-homepage-settings',
        'schema': '#autodescription-schema-settings',
        'robots': '#autodescription-robots-settings',
        'webmaster': '#autodescription-webmaster-settings',
        'sitemap': '#autodescription-sitemap-settings',
        'feed': '#autodescription-feed-settings'
    };

    var activeTargetId = sectionMap[currentSection] || '#autodescription-general-settings';

    // Build modern layout wrapper (tanpa sidebar kustom)
    var dashboardHtml = 
        '<div class="sgeobiz-dashboard">' +
        '    <div class="sgeobiz-main">' +
        '        <div class="sgeobiz-topbar">' +
        '             <h2 class="sgeobiz-topbar-title">Pengaturan SEO</h2>' +
        '             <div class="sgeobiz-topbar-actions"></div>' +
        '        </div>' +
        '        <div class="sgeobiz-settings-container"></div>' +
        '    </div>' +
        '</div>';

    var $form = $('#sgeobiz-settings');
    var $metaboxes = $('.sgeobiz-metaboxes');

    // Sembunyikan margin margin bawaan WP
    $metaboxes.addClass('sgeobiz-premium-theme');

    // Masukkan wrapper ke dalam form
    $form.prepend(dashboardHtml);

    // Bungkus elemen select dan label pendahulunya ke dalam sgeobiz-select-group agar sejajar di dalam grid
    $('.sgeobiz-fields').each(function() {
        var $fields = $(this);
        $fields.find('select').each(function() {
            var $select = $(this);
            var $prevLabel = $select.prev('label');
            if ($prevLabel.length) {
                $prevLabel.add($select).wrapAll('<div class="sgeobiz-select-group"></div>');
            }
        });
    });

    var $container = $('.sgeobiz-settings-container');
    var $actions = $('.sgeobiz-topbar-actions');

    // Pindahkan tombol simpan atas ke topbar actions
    $('.sgeobiz-top-buttons').contents().appendTo($actions);
    $('.sgeobiz-top-wrap').hide(); // Sembunyikan header lama

    // Move postboxes
    var activeTitle = '';
    var $postboxes = $('.postbox');
    $postboxes.each(function(index) {
        var $postbox = $(this);
        var title = $postbox.find('.hndle').text() || $postbox.find('h2').text() || 'Settings';
        var id = $postbox.attr('id') || 'postbox-' + index;

        // Clean up title
        title = title.replace(/[▼▲]/g, '').trim();

        // Move to container
        $postbox.appendTo($container);

        var isTabActive = ('#' + id === activeTargetId);
        if (isTabActive) {
            $postbox.removeClass('closed hidden').addClass('sgeobiz-active-postbox').show();
            activeTitle = title;
        } else {
            $postbox.hide();
        }
    });

    if (activeTitle) {
        $('.sgeobiz-topbar-title').text(activeTitle);
    }

    // Hide screen options / help links
    $('#screen-meta-links').hide();

    // Make sure toggle buttons still trigger native change checks
    $('input[type="checkbox"]').on('change', function() {
        if (window.sgeobizAys) {
            window.sgeobizAys.registerChange();
        }
    });
});
