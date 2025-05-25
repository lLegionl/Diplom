document.addEventListener('DOMContentLoaded', function() {
    // Выпадающее меню документов
    const menu = document.getElementById('documents-menu');
    if (menu) {
        menu.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A') {
                this.parentElement.classList.toggle('active');
                const icon = this.querySelector('.fa-chevron-down');
                icon.style.transform = this.parentElement.classList.contains('active') 
                    ? 'rotate(180deg)' 
                    : 'rotate(0deg)';
            }
        });
    }
});