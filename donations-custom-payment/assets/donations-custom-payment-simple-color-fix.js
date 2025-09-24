/**
 * Simple Color Picker with Alpha Slider - New Version
 */

document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure complete loading
    setTimeout(() => {
        const colorInputs = document.querySelectorAll('input[type="color"]');
        
        if (colorInputs.length === 0) return;
        
        // Add event listener for form submit
        const form = document.querySelector('#cpf-settings-form');
        if (form) {
            form.addEventListener('submit', function() {
                colorInputs.forEach(input => {
                    const alphaValue = input.dataset.alpha || '1';
                    const alphaInput = document.createElement('input');
                    alphaInput.type = 'hidden';
                    alphaInput.name = input.name + '_alpha';
                    alphaInput.value = alphaValue;
                    form.appendChild(alphaInput);
                });
            });
        }
        
        colorInputs.forEach((input, index) => {
            
            // Check if not already processed
            if (input.dataset.enhanced) return;
            input.dataset.enhanced = 'true';
            
            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.style.cssText = `
                display: flex;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
                margin: 5px 0;
                padding: 10px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                background: #f9f9f9;
            `;
            
            // Style the Color Input
            input.style.cssText = `
                width: 80px;
                height: 40px;
                border: 2px solid #ddd;
                border-radius: 6px;
                cursor: pointer;
                outline: none;
            `;
            
            // Create Alpha Container
            const alphaContainer = document.createElement('div');
            alphaContainer.style.cssText = `
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            
            const alphaLabel = document.createElement('label');
            alphaLabel.textContent = cpfColorStrings.alphaLabel;
            alphaLabel.style.cssText = `
                font-size: 12px;
                font-weight: 600;
                color: #555;
                min-width: 50px;
            `;
            
            const alphaSlider = document.createElement('input');
            alphaSlider.type = 'range';
            alphaSlider.min = '0';
            alphaSlider.max = '100';
            alphaSlider.value = '100';
            alphaSlider.style.cssText = `
                width: 120px;
                height: 20px;
                cursor: pointer;
                background: linear-gradient(to right, transparent, ${input.value});
            `;
            
            const alphaValue = document.createElement('span');
            alphaValue.textContent = '100%';
            alphaValue.style.cssText = `
                font-size: 12px;
                font-weight: 600;
                color: #333;
                min-width: 35px;
                background: #fff;
                padding: 2px 6px;
                border-radius: 4px;
                border: 1px solid #ddd;
            `;
            
            // Create Preview
            const preview = document.createElement('div');
            preview.style.cssText = `
                width: 40px;
                height: 40px;
                border: 2px solid #ddd;
                border-radius: 6px;
                position: relative;
                overflow: hidden;
            `;
            
            // Checkerboard pattern for background
            const bgPattern = document.createElement('div');
            bgPattern.style.cssText = `
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: 
                    linear-gradient(45deg, #ccc 25%, transparent 25%), 
                    linear-gradient(-45deg, #ccc 25%, transparent 25%), 
                    linear-gradient(45deg, transparent 75%, #ccc 75%), 
                    linear-gradient(-45deg, transparent 75%, #ccc 75%);
                background-size: 8px 8px;
                background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
            `;
            
            const colorFill = document.createElement('div');
            colorFill.style.cssText = `
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
            `;
            
            preview.appendChild(bgPattern);
            preview.appendChild(colorFill);
            
            // Preview update function
            function updatePreview() {
                const color = input.value;
                const alpha = alphaSlider.value / 100;
                
                // Convert hex to rgba
                const r = parseInt(color.slice(1, 3), 16);
                const g = parseInt(color.slice(3, 5), 16);
                const b = parseInt(color.slice(5, 7), 16);
                const rgba = `rgba(${r}, ${g}, ${b}, ${alpha})`;
                
                colorFill.style.backgroundColor = rgba;
                alphaValue.textContent = Math.round(alpha * 100) + '%';
                alphaSlider.style.background = `linear-gradient(to right, transparent, ${color})`;
                
                // Store alpha value in data attribute
                input.dataset.alpha = alpha;
            }
            
            // Event listeners
            input.addEventListener('change', updatePreview);
            input.addEventListener('input', updatePreview);
            alphaSlider.addEventListener('input', updatePreview);
            
            // Arrange elements
            alphaContainer.appendChild(alphaLabel);
            alphaContainer.appendChild(alphaSlider);
            alphaContainer.appendChild(alphaValue);
            
            // Store parent and input position
            const parentNode = input.parentNode;
            const nextSibling = input.nextSibling;
            
            // Create wrapper and add elements
            wrapper.appendChild(input);
            wrapper.appendChild(alphaContainer);
            wrapper.appendChild(preview);
            
            // Replace input with wrapper
            if (nextSibling) {
                parentNode.insertBefore(wrapper, nextSibling);
            } else {
                parentNode.appendChild(wrapper);
            }
            
            // Load saved alpha
            const savedAlpha = input.getAttribute('data-saved-alpha');
            if (savedAlpha) {
                alphaSlider.value = Math.round(savedAlpha * 100);
                input.dataset.alpha = savedAlpha;
            }
            
            // Initial update
            updatePreview();
        });
        
    }, 1000); // More time for complete loading
});