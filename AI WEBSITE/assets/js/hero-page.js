// Hero Page JavaScript for D2PT-style functionality

document.addEventListener('DOMContentLoaded', function() {
    initPositionTabs();
    initMainTabs();
    initViewToggles();
    initTrendCharts();
});

// Position Tabs (Carry, Mid, etc.)
function initPositionTabs() {
    const posTabs = document.querySelectorAll('.d2pt-pos-tab, .mobile-pos-btn');
    const roleSections = document.querySelectorAll('.d2pt-role-content');
    
    posTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const position = this.dataset.position;
            
            // Remove active from all tabs
            document.querySelectorAll('.d2pt-pos-tab, .mobile-pos-btn').forEach(t => {
                t.classList.remove('active');
            });
            
            // Add active to clicked tab and corresponding mobile/desktop tab
            document.querySelectorAll(`[data-position="${position}"]`).forEach(t => {
                t.classList.add('active');
            });
            
            // Show corresponding role section
            roleSections.forEach(section => {
                if (section.dataset.roleSection === position) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        });
    });
}

// Main Tabs (Builds, Meta Analysis, etc.)
function initMainTabs() {
    const mainTabs = document.querySelectorAll('.d2pt-main-tab');
    const mainContents = document.querySelectorAll('.d2pt-main-content');
    
    mainTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.mainTab;
            const parentSection = this.closest('.d2pt-role-content');
            
            // Remove active from tabs in this section
            parentSection.querySelectorAll('.d2pt-main-tab').forEach(t => {
                t.classList.remove('active');
            });
            
            // Add active to clicked tab
            this.classList.add('active');
            
            // Show corresponding content in this section
            parentSection.querySelectorAll('.d2pt-main-content').forEach(content => {
                if (content.dataset.mainContent === tabName) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    });
}

// View Toggles (Normal View / Table View)
function initViewToggles() {
    const viewBtns = document.querySelectorAll('.view-btn');
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            const parentSection = this.closest('.d2pt-role-content');
            
            // Remove active from all view buttons in this section
            parentSection.querySelectorAll('.view-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            // Add active to clicked button
            this.classList.add('active');
            
            // Handle fullscreen
            if (view === 'fullscreen') {
                parentSection.requestFullscreen().catch(err => {
                    console.error('Fullscreen error:', err);
                });
            }
        });
    });
}

// Trend Charts
function initTrendCharts() {
    const winRateCanvas = document.getElementById('winRateTrend');
    const pickRateCanvas = document.getElementById('pickRateTrend');
    
    if (winRateCanvas) {
        const ctx = winRateCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
                datasets: [{
                    data: [45, 46, 47, 46.5, 47, 47.7, 46.4, 47.7],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false, min: 40, max: 55 }
                }
            }
        });
    }
    
    if (pickRateCanvas) {
        const ctx = pickRateCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
                datasets: [{
                    data: [7.6, 7.2, 6.8, 5.5, 4.2, 3.1, 1.5, 0],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false, min: 0, max: 10 }
                }
            }
        });
    }
}
