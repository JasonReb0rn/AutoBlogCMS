// Create a single modal element to be reused
function createImageModal() {
    const modal = document.createElement('div');
    modal.id = 'image-modal';
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <img src="" alt="" />
            <figcaption></figcaption>
            <button class="close-modal">&times;</button>
        </div>
    `;
    
    // Close modal on click outside or close button
    modal.onclick = (e) => {
        if (e.target === modal || e.target.className === 'close-modal') {
            modal.style.display = 'none';
        }
    };
    
    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            modal.style.display = 'none';
        }
    });
    
    return modal;
}

// Initialize enlargeable images
function initializeEnlargeableImages() {
    // Create modal if it doesn't exist
    let modal = document.getElementById('image-modal');
    if (!modal) {
        modal = createImageModal();
        document.body.appendChild(modal);
    }
    
    // Add click handlers to all enlargeable images
    document.querySelectorAll('.blog-image-wrapper[data-enlargeable="true"]').forEach(wrapper => {
        wrapper.onclick = () => {
            const img = wrapper.querySelector('.blog-content-image');
            const caption = wrapper.querySelector('.blog-image-caption');
            
            // Update and show modal
            const modalImg = modal.querySelector('img');
            const modalCaption = modal.querySelector('figcaption');
            modalImg.src = img.src;
            modalImg.alt = img.alt;
            modalCaption.textContent = caption ? caption.textContent : '';
            modal.style.display = 'flex';
        };
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeEnlargeableImages);