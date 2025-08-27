jQuery(document).ready(function($) {
    // Gallery Media Uploader
    if (typeof wp !== 'undefined' && wp.media) {
        var wprGalleryFrame;
        var $galleryButton = $('.wpr-add-gallery-images');
        var $galleryInput = $('#wpr_gallery');
        var $galleryPreview = $('#wpr_gallery_preview');

        $galleryButton.on('click', function(e) {
            e.preventDefault();

            // If the media frame already exists, reopen it.
            if (wprGalleryFrame) {
                wprGalleryFrame.open();
                return;
            }

            // Create a new media frame
            wprGalleryFrame = wp.media({
                title: WPR_Metabox.i18n.choose_images,
                button: {
                    text: WPR_Metabox.i18n.add_images
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });

            // When an image is selected in the media frame...
            wprGalleryFrame.on('select', function() {
                // Get media attachment details from the frame state
                var attachments = wprGalleryFrame.state().get('selection').toJSON();
                var attachmentIds = [];
                
                // Clear preview
                $galleryPreview.empty();
                
                // Loop through each attachment and create preview
                $.each(attachments, function(i, attachment) {
                    attachmentIds.push(attachment.id);
                    
                    // Create preview image
                    var previewImg = $('<div>')
                        .addClass('wpr-gallery-item')
                        .attr('data-id', attachment.id)
                        .css({
                            'width': '90px',
                            'position': 'relative',
                            'margin': '5px'
                        });
                    
                    var img = $('<img>')
                        .attr('src', attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url)
                        .css({
                            'width': '100%',
                            'height': 'auto',
                            'display': 'block',
                            'border': '1px solid #ddd',
                            'padding': '2px',
                            'border-radius': '4px'
                        });
                    
                    var removeBtn = $('<button>')
                        .text(WPR_Metabox.i18n.remove)
                        .addClass('button button-small')
                        .css({
                            'position': 'absolute',
                            'top': '5px',
                            'right': '5px',
                            'background': 'rgba(255,0,0,0.8)',
                            'color': 'white',
                            'border': 'none',
                            'padding': '2px 5px',
                            'font-size': '10px',
                            'cursor': 'pointer'
                        })
                        .on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $(this).parent().remove();
                            updateGalleryInput();
                        });
                    
                    previewImg.append(img).append(removeBtn);
                    $galleryPreview.append(previewImg);
                });

                // Update the hidden input field
                $galleryInput.val(attachmentIds.join(','));
            });

            // Finally, open the modal on click
            wprGalleryFrame.open();
        });

        // Function to update gallery input when images are removed
        function updateGalleryInput() {
            var ids = [];
            $('.wpr-gallery-item').each(function() {
                ids.push($(this).data('id'));
            });
            $galleryInput.val(ids.join(','));
        }

        // Add remove functionality to existing images
        $('.wpr-gallery-item').each(function() {
            var $item = $(this);
            var removeBtn = $('<button>')
                .text(WPR_Metabox.i18n.remove)
                .addClass('button button-small')
                .css({
                    'position': 'absolute',
                    'top': '5px',
                    'right': '5px',
                    'background': 'rgba(255,0,0,0.8)',
                    'color': 'white',
                    'border': 'none',
                    'padding': '2px 5px',
                    'font-size': '10px',
                    'cursor': 'pointer'
                })
                .on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $item.remove();
                    updateGalleryInput();
                });
            
            $item.css('position', 'relative').append(removeBtn);
        });
    }

    // Map functionality (if needed)
    // This would require Google Maps API key to be set in plugin settings
    if (typeof google !== 'undefined') {
        // Initialize map if coordinates are provided
        var lat = $('#wpr_lat').val();
        var lng = $('#wpr_lng').val();
        
        if (lat && lng) {
            $('#wpr_map_canvas').show();
            var map = new google.maps.Map(document.getElementById('wpr_map_canvas'), {
                center: { lat: parseFloat(lat), lng: parseFloat(lng) },
                zoom: 15
            });
            
            new google.maps.Marker({
                position: { lat: parseFloat(lat), lng: parseFloat(lng) },
                map: map,
                title: 'Property Location'
            });
        }
    }
});
