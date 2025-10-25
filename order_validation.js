// Modal Functions
function openOrderModal() {
    document.getElementById('orderModal').classList.add('active');
}

function closeOrderModal() {
    // Redirect back to dashboard when modal is closed
    window.location.href = 'dashboard.php';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target === modal) {
        closeOrderModal();
    }
};

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeOrderModal();
    }
});

// Delivery Toggle + Pricing
function toggleDeliveryLocation() {
    console.log('toggleDeliveryLocation called');
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked');
    const deliveryLocationGroup = document.getElementById('deliveryLocationGroup');
    const deliveryLocationInput = document.getElementById('delivery_location');
    const deliveryFeeRow = document.getElementById('deliveryFeeRow');

    if (deliveryOption && deliveryLocationGroup && deliveryLocationInput && deliveryFeeRow) {
        console.log('Delivery option:', deliveryOption.value);

        if (deliveryOption.value === 'delivery') {
            // Show delivery location field and make it required
            deliveryLocationGroup.style.display = 'block';
            deliveryLocationInput.required = true;
            deliveryFeeRow.style.display = 'flex';
        } else {
            // Hide delivery location field and remove requirement
            deliveryLocationGroup.style.display = 'none';
            deliveryLocationInput.required = false;
            deliveryLocationInput.value = '';
            deliveryFeeRow.style.display = 'none';
        }
        calculatePrice();
    }
}

function calculatePrice() {
    console.log('calculatePrice called');
    const cylinderSelect = document.getElementById('cylinder_type');
    if (!cylinderSelect) return;

    const selectedOption = cylinderSelect.options[cylinderSelect.selectedIndex];
    const price = parseInt(selectedOption.getAttribute('data-price')) || 0;
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked');
    
    // Calculate subtotal
    const subtotal = price * quantity;

    // Delivery fee (temporary fixed base)
    let deliveryFee = 0;
    if (deliveryOption && deliveryOption.value === 'delivery') {
        deliveryFee = 150; 
    }
    
    const total = subtotal + deliveryFee;

    // Update displayed values
    const cylinderPrice = document.getElementById('cylinderPrice');
    if (cylinderPrice) {
        cylinderPrice.textContent = 'KSH ' + price.toLocaleString();
        document.getElementById('quantityDisplay').textContent = quantity;
        document.getElementById('subtotal').textContent = 'KSH ' + subtotal.toLocaleString();
        document.getElementById('deliveryFeeDisplay').textContent = 'KSH ' + deliveryFee.toFixed(2);
        // Update the hidden input for form submission
        const deliveryFeeInput = document.getElementById('deliveryFeeInput');
        if (deliveryFeeInput) {
            deliveryFeeInput.value = deliveryFee;
        }
        document.getElementById('totalPrice').textContent = 'KSH ' + total.toFixed(2);
    }
}

//  Validation Logic
function validateForm() {
    console.log('Validating form...');
    const cylinderType = document.getElementById('cylinder_type');
    const quantity = document.getElementById('quantity');
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked');
    const deliveryLocation = document.getElementById('delivery_location');
    const customDetails = document.getElementById('custom_details');
    
    const errors = [];

    // Cylinder type
    if (!cylinderType.value) {
        errors.push('Please select a cylinder size');
        highlightError(cylinderType);
    } else {
        removeError(cylinderType);
    }

    // Quantity
    if (!quantity.value || quantity.value < 1) {
        errors.push('Quantity must be at least 1');
        highlightError(quantity);
    } else {
        removeError(quantity);
    }

    // Delivery location if delivery selected
    if (deliveryOption && deliveryOption.value === 'delivery') {
        if (!deliveryLocation.value.trim()) {
            errors.push('Please enter delivery location');
            highlightError(deliveryLocation);
        } else {
            removeError(deliveryLocation);
        }
    }

    // Custom details
    if (cylinderType.value === 'custom') {
        if (!customDetails.value.trim()) {
            errors.push('Please provide custom order details');
            highlightError(customDetails);
        } else {
            removeError(customDetails);
        }
    }

    // Display errors or pass validation
    if (errors.length > 0) {
        displayErrors(errors);
        return false;
    } else {
        clearErrors();
        return true;
    }
}

//  Error Handling Helpers
function highlightError(field) {
    field.style.borderColor = '#f44336';
    field.style.backgroundColor = '#fff5f5';

    let errorElement = field.parentNode.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.color = '#f44336';
        errorElement.style.fontSize = '12px';
        errorElement.style.marginTop = '5px';
        field.parentNode.appendChild(errorElement);
    }

    const messages = {
        'cylinder_type': 'Please select a cylinder size',
        'quantity': 'Quantity must be at least 1',
        'delivery_location': 'Delivery location is required',
        'custom_details': 'Custom order details are required'
    };
    errorElement.textContent = messages[field.id] || 'Invalid field';
}

function removeError(field) {
    field.style.borderColor = '#e0e6ed';
    field.style.backgroundColor = '';
    const errorElement = field.parentNode.querySelector('.error-message');
    if (errorElement) errorElement.remove();
}

function displayErrors(errors) {
    clearErrors();
    const errorContainer = document.createElement('div');
    errorContainer.id = 'form-errors';
    errorContainer.style.background = '#fff5f5';
    errorContainer.style.border = '1px solid #f44336';
    errorContainer.style.borderRadius = '10px';
    errorContainer.style.padding = '15px';
    errorContainer.style.marginBottom = '20px';
    errorContainer.style.color = '#f44336';

    const title = document.createElement('strong');
    title.textContent = 'Please fix the following errors:';
    title.style.display = 'block';
    title.style.marginBottom = '10px';
    errorContainer.appendChild(title);

    const list = document.createElement('ul');
    list.style.margin = '0';
    list.style.paddingLeft = '20px';
    errors.forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        li.style.marginBottom = '5px';
        list.appendChild(li);
    });

    errorContainer.appendChild(list);
    const form = document.getElementById('orderForm');
    form.parentNode.insertBefore(errorContainer, form);
    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearErrors() {
    const errorContainer = document.getElementById('form-errors');
    if (errorContainer) errorContainer.remove();

    document.querySelectorAll('.error-message').forEach(e => e.remove());
    document.querySelectorAll('select, input').forEach(field => {
        field.style.borderColor = '#e0e6ed';
        field.style.backgroundColor = '';
    });
}

//  Real-Time Validation
function setupRealTimeValidation() {
    const cylinderType = document.getElementById('cylinder_type');
    const quantity = document.getElementById('quantity');
    const deliveryLocation = document.getElementById('delivery_location');
    const customDetails = document.getElementById('custom_details');
    const deliveryOptions = document.querySelectorAll('input[name="delivery_option"]');

    if (cylinderType) cylinderType.addEventListener('change', () => removeError(cylinderType));
    if (quantity) quantity.addEventListener('input', () => removeError(quantity));
    if (deliveryLocation) deliveryLocation.addEventListener('input', () => removeError(deliveryLocation));
    if (customDetails) customDetails.addEventListener('input', () => removeError(customDetails));

    deliveryOptions.forEach(option => {
        option.addEventListener('change', function() {
            const deliveryLocation = document.getElementById('delivery_location');
            if (this.value === 'delivery' && !deliveryLocation.value.trim()) {
                highlightError(deliveryLocation);
            } else {
                removeError(deliveryLocation);
            }
        });
    });
}

//  Form Submission Handler
function handleFormSubmit() {
    console.log('Form submission handled');
    if (validateForm()) {
        console.log('Form validation passed, submitting...');
        document.getElementById('orderForm').submit();
    } else {
        console.log('Form validation failed');
    } 
        
}

//  Initialization on Page Loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('order_validation.js fully loaded and DOM ready');

    const orderModal = document.getElementById('orderModal');
    if (orderModal) openOrderModal();

    const form = document.getElementById('orderForm');
    const button = document.querySelector('.submit-btn');
    const cylinderSelect = document.getElementById('cylinder_type');
    const quantityInput = document.getElementById('quantity');
    const customOrderGroup = document.getElementById('customOrderGroup');
    const deliveryOptions = document.querySelectorAll('input[name="delivery_option"]');

    if (button && form) {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            handleFormSubmit();
        });
    }

    deliveryOptions.forEach(option => {
        option.addEventListener('change', () => {
            console.log('Delivery option changed to:', option.value);
            toggleDeliveryLocation();
        });
    });

    if (cylinderSelect) {
        cylinderSelect.addEventListener('change', function() {
            customOrderGroup.style.display = (this.value === 'custom') ? 'block' : 'none';
            calculatePrice();
        });
    }

    if (quantityInput) {
        quantityInput.addEventListener('input', calculatePrice);
    }

    // Initial setup
    toggleDeliveryLocation();
    calculatePrice();
    setupRealTimeValidation();
});
