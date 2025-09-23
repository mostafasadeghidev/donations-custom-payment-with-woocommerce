/**
 * Admin panel scripts for Custom Payment Form
 */

document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.cpf-tab-link');
    const tabContents = document.querySelectorAll('.cpf-tab-content');
    
    // Check if tabs exist
    if (tabLinks.length === 0 || tabContents.length === 0) return;
    
    // Tab switching function
    function switchTab(targetTab) {
        // Hide all tabs
        tabContents.forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
            content.style.visibility = 'hidden';
            content.style.opacity = '0';
            content.style.position = 'absolute';
        });
        
        // Remove active class from all links
        tabLinks.forEach(link => {
            link.classList.remove('active');
            link.style.backgroundColor = '#f1f1f1';
            link.style.color = '#333';
        });
        
        // Activate target tab
        const targetElement = document.getElementById('tab-' + targetTab);
        const targetLink = document.querySelector(`[data-tab="${targetTab}"]`);
        
        if (targetElement && targetLink) {
            targetElement.classList.add('active');
            targetElement.style.display = 'block';
            targetElement.style.visibility = 'visible';
            targetElement.style.opacity = '1';
            targetElement.style.position = 'static';
            
            targetLink.classList.add('active');
            targetLink.style.backgroundColor = '#0073aa';
            targetLink.style.color = '#fff';
        }
    }
    
    // Add event listener to tabs
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            switchTab(targetTab);
        });
    });
    
    // Activate first tab
    if (tabLinks.length > 0) {
        const firstTab = tabLinks[0].getAttribute('data-tab');
        switchTab(firstTab);
    }
    
    // Product image selection
    const selectImageBtn = document.getElementById('donations_custom_payment_select_image');
    const removeImageBtn = document.getElementById('donations_custom_payment_remove_image');
    const imagePreview = document.getElementById('donations_custom_payment_product_image_preview');
    const imageIdInput = document.getElementById('donations_custom_payment_product_image_id');
    
    if (selectImageBtn) {
        selectImageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if wp.media exists
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('Error: Media library is not available');
                return;
            }
            
            // Create media frame
            const frame = wp.media({
                title: 'Select Product Image',
                button: {
                    text: 'Select'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                
                // Update preview
                if (attachment.sizes && attachment.sizes.thumbnail) {
                    imagePreview.innerHTML = `<img src="${attachment.sizes.thumbnail.url}" style="max-width: 100%; max-height: 100%; object-fit: cover;">`;
                } else {
                    imagePreview.innerHTML = `<img src="${attachment.url}" style="max-width: 100%; max-height: 100%; object-fit: cover;">`;
                }
                
                // Update input
                imageIdInput.value = attachment.id;
                
                // Show remove button
                if (removeImageBtn) {
                    removeImageBtn.style.display = 'inline-block';
                }
            });
            
            frame.open();
        });
    }
    
    // Remove product image
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear preview
            imagePreview.innerHTML = '<span style="color: #999; font-size: 12px;">No Image</span>';
            
            // Clear input
            imageIdInput.value = '';
            
            // Hide remove button
            this.style.display = 'none';
        });
    }
    

});