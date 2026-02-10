/**
 * Student Dashboard - Main JavaScript
 * Gère les interactions côté client pour l'interface élève
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialisation des popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Gestion du menu mobile
    const sidebarToggler = document.querySelector('[data-bs-toggle="offcanvas"]');
    if (sidebarToggler) {
        sidebarToggler.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.createElement('div');
            backdrop.className = 'offcanvas-backdrop fade show d-lg-none';
            document.body.appendChild(backdrop);
            
            sidebar.addEventListener('hidden.bs.offcanvas', function() {
                document.body.removeChild(backdrop);
            }, { once: true });
        });
    }

    // Gestion de la soumission des formulaires avec confirmation
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Gestion de l'aperçu de l'image de profil
    const profileImageInput = document.getElementById('profile_image');
    const profileImagePreview = document.getElementById('profile_image_preview');
    
    if (profileImageInput && profileImagePreview) {
        profileImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                    profileImagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Gestion des onglets avec stockage de l'état
    const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabLinks.forEach(tabLink => {
        tabLink.addEventListener('click', function(e) {
            localStorage.setItem('lastTab', this.getAttribute('href'));
        });
        
        // Restauration de l'onglet actif
        const lastTab = localStorage.getItem('lastTab');
        if (lastTab && tabLink.getAttribute('href') === lastTab) {
            const tab = new bootstrap.Tab(tabLink);
            tab.show();
        }
    });

    // Gestion des formulaires en AJAX
    const ajaxForms = document.querySelectorAll('form.ajax-form');
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Afficher l'indicateur de chargement
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
            
            fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.message) {
                    showAlert('success', data.message);
                    // Recharger la page après un court délai
                    setTimeout(() => window.location.reload(), 1500);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('danger', 'Une erreur est survenue. Veuillez réessayer.');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    });

    // Fonction pour afficher des alertes
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        `;
        
        const container = document.querySelector('.alerts-container') || document.querySelector('main');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Fermer automatiquement après 5 secondes
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
            alert.close();
        }, 5000);
    }

    // Gestion des cartes cliquables
    const clickableCards = document.querySelectorAll('.clickable-card');
    clickableCards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e) {
            // Ne pas déclencher si l'utilisateur a cliqué sur un bouton ou un lien
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a, button')) {
                return;
            }
            
            const link = this.querySelector('a.card-link');
            if (link) {
                window.location.href = link.href;
            }
        });
    });

    // Initialisation des sélecteurs de date
    const datePickers = document.querySelectorAll('.datepicker');
    if (datePickers.length > 0) {
        // Si vous utilisez un plugin de datepicker comme flatpickr, vous pouvez l'initialiser ici
        // import("flatpickr").then(flatpickr => {
        //     flatpickr(".datepicker", {
        //         dateFormat: "Y-m-d",
        //         locale: "fr"
        //     });
        // });
    }

    // Gestion du mode sombre (optionnel)
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });

        // Vérifier le mode stocké
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }

    // Gestion des menus déroulants personnalisés
    const customSelects = document.querySelectorAll('.custom-select');
    customSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.style.color = this.value ? '#212529' : '#6c757d';
        });
    });

    // Initialisation des graphiques (si Chart.js est inclus)
    if (typeof Chart !== 'undefined') {
        const chartElements = document.querySelectorAll('.chart-container');
        chartElements.forEach(chartElement => {
            const ctx = chartElement.getContext('2d');
            const chartType = chartElement.dataset.chartType || 'line';
            const chartData = JSON.parse(chartElement.dataset.chartData || '{}');
            
            new Chart(ctx, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        });
    }

    // Gestion des modaux de confirmation
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Gestion des champs de recherche avec délai
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        let timeoutId;
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const form = this.closest('form');
            if (form) {
                timeoutId = setTimeout(() => {
                    form.submit();
                }, 500);
            }
        });
    });

    // Gestion des onglets de navigation mobile
    const mobileTabs = document.querySelectorAll('.mobile-tab');
    mobileTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            document.querySelectorAll('.mobile-tab').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
            document.getElementById(target).classList.add('show', 'active');
        });
    });

    // Initialisation des tooltips dynamiques
    document.body.addEventListener('mouseover', function(e) {
        const element = e.target.closest('[title]');
        if (element && !element.hasAttribute('data-bs-toggle')) {
            element.setAttribute('data-bs-toggle', 'tooltip');
            element.setAttribute('data-bs-placement', 'top');
            new bootstrap.Tooltip(element);
            // Déclencher manuellement le tooltip
            const tooltip = bootstrap.Tooltip.getInstance(element);
            if (tooltip) {
                tooltip.show();
            }
        }
    });

    // Gestion du chargement paresseux des images
    const lazyImages = document.querySelectorAll('img.lazy');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback pour les navigateurs qui ne supportent pas IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
});

// Fonction utilitaire pour formater les dates
function formatDate(dateString, format = 'fr-FR') {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString(format, options);
}

// Fonction pour afficher un toast de notification
function showToast(type, message, title = '') {
    const toastContainer = document.getElementById('toast-container') || (() => {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    })();

    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.role = 'alert';
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${title ? `<strong>${title}</strong><br>` : ''}
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Exposer les fonctions globales
window.showToast = showToast;
window.formatDate = formatDate;
