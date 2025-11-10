document.addEventListener('DOMContentLoaded', function() {
    initRoleTabs();
    initFilterButtons();
    initHeaderSearch();
    initPageSearch();
    initPagination();
    initCarousel();
    initWinRateChart();
    initKeyboardShortcuts();
    initHeroFilters();
    initPlayerFilters();
    initTournamentFilters();
});

let searchDebounceTimer;

function initHeaderSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    const searchResults = document.createElement('div');
    searchResults.id = 'searchResults';
    searchResults.className = 'search-results-dropdown';
    searchResults.style.display = 'none';
    searchInput.parentElement.appendChild(searchResults);

    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        clearTimeout(searchDebounceTimer);
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchDebounceTimer = setTimeout(() => {
            performSearch(query, searchResults);
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (searchResults.children.length > 0) {
            searchResults.style.display = 'block';
        }
    });
}

function performSearch(query, resultsContainer) {
    fetch(`/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data, resultsContainer);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

function displaySearchResults(data, container) {
    container.innerHTML = '';
    
    let hasResults = false;
    
    if (data.heroes && data.heroes.length > 0) {
        hasResults = true;
        const heroSection = document.createElement('div');
        heroSection.className = 'search-section';
        heroSection.innerHTML = '<div class="search-section-title"><i class="fas fa-mask"></i> Heroes</div>';
        
        data.heroes.forEach(hero => {
            const item = document.createElement('a');
            item.href = hero.url;
            item.className = 'search-result-item';
            item.innerHTML = `
                <img src="${hero.image}" alt="${hero.name}" class="search-result-avatar">
                <span class="search-result-name">${hero.name}</span>
            `;
            heroSection.appendChild(item);
        });
        
        container.appendChild(heroSection);
    }
    
    if (data.players && data.players.length > 0) {
        hasResults = true;
        const playerSection = document.createElement('div');
        playerSection.className = 'search-section';
        playerSection.innerHTML = '<div class="search-section-title"><i class="fas fa-user"></i> Players</div>';
        
        data.players.forEach(player => {
            const item = document.createElement('a');
            item.href = player.url;
            item.className = 'search-result-item';
            item.innerHTML = `
                <img src="${player.avatar}" alt="${player.name}" class="search-result-avatar">
                <span class="search-result-name">${player.name}</span>
            `;
            playerSection.appendChild(item);
        });
        
        container.appendChild(playerSection);
    }
    
    if (!hasResults) {
        container.innerHTML = '<div class="search-no-results"><i class="fas fa-search"></i> No results found</div>';
    }
    
    container.style.display = 'block';
}

function initHeroFilters() {
    const roleTabs = document.querySelectorAll('.role-tab');
    if (roleTabs.length === 0) return;
    
    roleTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const role = this.textContent.trim();
            const heroCards = document.querySelectorAll('.hero-card');
            const heroRows = document.querySelectorAll('.heroes-table tbody tr');
            
            if (role === 'Overall') {
                heroCards.forEach(card => card.style.display = '');
                heroRows.forEach(row => row.style.display = '');
                return;
            }
            
            heroCards.forEach(card => {
                const cardRole = card.querySelector('.badge-role')?.textContent || '';
                if (cardRole.includes(role) || role === 'Overall') {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
            
            heroRows.forEach(row => {
                const rowRole = row.querySelector('.hero-badge')?.textContent || '';
                if (rowRole.includes(role) || role === 'Overall') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

function initPlayerFilters() {
    const filterButtons = document.querySelectorAll('.players-page-section .filter-btn');
    if (filterButtons.length === 0) return;
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const region = this.textContent.trim().toLowerCase();
            const playerCards = document.querySelectorAll('.player-card');
            
            playerCards.forEach(card => {
                const countryCode = card.querySelector('.meta-item .fa-flag + span')?.textContent.toLowerCase() || '';
                
                if (region.includes('all')) {
                    card.style.display = '';
                } else if (region.includes('americas')) {
                    if (['us', 'ca', 'br', 'ar', 'mx', 'pe', 'cl'].some(code => countryCode.includes(code))) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                } else if (region.includes('europe')) {
                    if (['gb', 'de', 'fr', 'se', 'dk', 'no', 'fi', 'ru', 'ua', 'pl', 'ro', 'bg'].some(code => countryCode.includes(code))) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                } else if (region.includes('asia')) {
                    if (['cn', 'jp', 'kr', 'tw'].some(code => countryCode.includes(code))) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                } else if (region.includes('sea')) {
                    if (['ph', 'my', 'sg', 'th', 'vn', 'id'].some(code => countryCode.includes(code))) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });
}

function initTournamentFilters() {
    const filterButtons = document.querySelectorAll('.tournaments-page-section .filter-btn');
    if (filterButtons.length === 0) return;
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.textContent.trim().toLowerCase();
            const tournamentCards = document.querySelectorAll('.tournament-card');
            
            tournamentCards.forEach(card => {
                if (status.includes('all')) {
                    card.style.display = '';
                } else {
                    card.style.display = '';
                }
            });
        });
    });
}

function initPageSearch() {
    const heroSearchInput = document.getElementById('heroSearch');
    if (heroSearchInput) {
        heroSearchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const heroRows = document.querySelectorAll('.heroes-table tbody tr');
            const heroCards = document.querySelectorAll('.hero-card');
            
            heroRows.forEach(row => {
                const heroName = row.querySelector('.hero-name')?.textContent.toLowerCase() || '';
                row.style.display = heroName.includes(query) ? '' : 'none';
            });
            
            heroCards.forEach(card => {
                const heroName = card.querySelector('.hero-name')?.textContent.toLowerCase() || '';
                card.style.display = heroName.includes(query) ? '' : 'none';
            });
        });
    }

    const playerSearchInput = document.getElementById('playerSearch');
    if (playerSearchInput) {
        playerSearchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const playerCards = document.querySelectorAll('.player-card');
            
            playerCards.forEach(card => {
                const playerName = card.querySelector('.player-name-display')?.textContent.toLowerCase() || '';
                const teamName = card.querySelector('.player-team')?.textContent.toLowerCase() || '';
                const display = (playerName.includes(query) || teamName.includes(query)) ? '' : 'none';
                card.style.display = display;
            });
        });
    }
}

function initRoleTabs() {
    const tabs = document.querySelectorAll('.role-tab, .filter-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const parent = this.parentElement;
            const role = this.dataset.role;
            
            // Update active tab
            parent.querySelectorAll('.role-tab, .filter-tab').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
            
            // Show corresponding content
            const contentSection = this.closest('section, .section');
            if (contentSection) {
                // Hide all role content
                contentSection.querySelectorAll('.role-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show selected role content
                const targetContent = contentSection.querySelector(`[data-role-content="${role}"]`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            }
        });
    });
}

function initFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

function initPagination() {
    const pageButtons = document.querySelectorAll('.page-num');
    pageButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.querySelectorAll('.page-num').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

function initCarousel() {
    const carouselBtns = document.querySelectorAll('.carousel-btn');
    carouselBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const carousel = this.closest('.section').querySelector('.tournaments-carousel');
            if (!carousel) return;
            
            const scrollAmount = 300;
            if (this.classList.contains('prev')) {
                carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        });
    });
}

function initWinRateChart() {
    const canvas = document.getElementById('winRateChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;

    ctx.fillStyle = '#1a2332';
    ctx.fillRect(0, 0, width, height);

    const radiantData = generateWinRateData(54, 50);
    const direData = generateWinRateData(46, 50);

    ctx.strokeStyle = '#92c353';
    ctx.lineWidth = 2;
    ctx.beginPath();
    radiantData.forEach((point, i) => {
        const x = (i / (radiantData.length - 1)) * width;
        const y = height - (point / 100) * height;
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();

    ctx.strokeStyle = '#c23c2a';
    ctx.lineWidth = 2;
    ctx.beginPath();
    direData.forEach((point, i) => {
        const x = (i / (direData.length - 1)) * width;
        const y = height - (point / 100) * height;
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();

    radiantData.forEach((point, i) => {
        const x = (i / (radiantData.length - 1)) * width;
        const y = height - (point / 100) * height;
        
        ctx.fillStyle = point > 50 ? '#10b981' : '#ef4444';
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, Math.PI * 2);
        ctx.fill();
    });

    direData.forEach((point, i) => {
        const x = (i / (direData.length - 1)) * width;
        const y = height - (point / 100) * height;
        
        ctx.fillStyle = point < 50 ? '#10b981' : '#ef4444';
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, Math.PI * 2);
        ctx.fill();
    });
}

function generateWinRateData(baseRate, count) {
    const data = [];
    for (let i = 0; i < count; i++) {
        const variance = (Math.random() - 0.5) * 8;
        data.push(Math.max(20, Math.min(80, baseRate + variance)));
    }
    return data;
}

function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        if (e.key === 'Escape') {
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.style.display = 'none';
            }
        }
    });
}

document.querySelectorAll('.open-match-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        alert('Match details page coming soon!');
    });
});

document.querySelectorAll('.view-build-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        alert('Build details page coming soon!');
    });
});

document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const parent = this.parentElement;
        parent.querySelectorAll('.toggle-btn').forEach(b => {
            b.classList.remove('active');
        });
        this.classList.add('active');
    });
});

// Role Tabs Functionality
document.addEventListener('DOMContentLoaded', function() {
    const roleTabs = document.querySelectorAll('.role-tab');
    const roleContents = document.querySelectorAll('.role-content');
    
    roleTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const role = this.dataset.role;
            
            // Remove active from all tabs and contents
            roleTabs.forEach(t => t.classList.remove('active'));
            roleContents.forEach(c => c.classList.remove('active'));
            
            // Add active to clicked tab and corresponding content
            this.classList.add('active');
            const content = document.querySelector(`[data-role-content="${role}"]`);
            if (content) {
                content.classList.add('active');
            }
        });
    });
});

// Handle broken ability/item images with placeholders
document.addEventListener('DOMContentLoaded', function() {
    // Fallback for broken ability icons
    document.querySelectorAll('.ability-icon, .ability-icon-sm').forEach(img => {
        img.addEventListener('error', function() {
            // Use Font Awesome icon as fallback
            const placeholder = document.createElement('div');
            placeholder.className = this.className + ' ability-placeholder';
            placeholder.innerHTML = '<i class="fas fa-magic"></i>';
            placeholder.style.cssText = 'width: ' + this.width + 'px; height: ' + this.height + 'px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);';
            this.parentNode.replaceChild(placeholder, this);
        });
    });
    
    // Fallback for broken item icons
    document.querySelectorAll('img[src*="/items/"]').forEach(img => {
        img.addEventListener('error', function() {
            const placeholder = document.createElement('div');
            placeholder.className = this.className + ' item-placeholder';
            placeholder.innerHTML = '<i class="fas fa-cube"></i>';
            placeholder.style.cssText = 'width: ' + this.width + 'px; height: ' + this.height + 'px; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 2px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 20px;';
            this.parentNode.replaceChild(placeholder, this);
        });
    });
});
