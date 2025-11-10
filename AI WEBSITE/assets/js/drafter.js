// Drafter State Management
const drafterState = {
    radiant: {
        picks: [],
        bans: []
    },
    dire: {
        picks: [],
        bans: []
    },
    currentPhase: 'ban',  // 'ban' or 'pick'
    currentTurn: 'radiant', // 'radiant' or 'dire'
    phaseIndex: 0,
    draftSequence: [
        // Phase 1: Initial 4 Bans (Radiant-Dire-Radiant-Dire)
        { team: 'radiant', action: 'ban' },
        { team: 'dire', action: 'ban' },
        { team: 'radiant', action: 'ban' },
        { team: 'dire', action: 'ban' },
        
        // Phase 2: First 6 Picks (Radiant-Dire-Dire-Radiant-Radiant-Dire)
        { team: 'radiant', action: 'pick' },
        { team: 'dire', action: 'pick' },
        { team: 'dire', action: 'pick' },
        { team: 'radiant', action: 'pick' },
        { team: 'radiant', action: 'pick' },
        { team: 'dire', action: 'pick' },
        
        // Phase 3: Second 4 Bans (Dire-Radiant-Dire-Radiant)
        { team: 'dire', action: 'ban' },
        { team: 'radiant', action: 'ban' },
        { team: 'dire', action: 'ban' },
        { team: 'radiant', action: 'ban' },
        
        // Phase 4: Final 4 Picks (Dire-Radiant-Radiant-Dire)
        { team: 'dire', action: 'pick' },
        { team: 'radiant', action: 'pick' },
        { team: 'radiant', action: 'pick' },
        { team: 'dire', action: 'pick' }
    ]
};

// Hero counter data (simplified - would be from API in production)
const heroCounters = {
    // Format: heroId: [counter ids with advantage scores]
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initializeFilters();
    updatePhaseDisplay();
    initializeTabs();
});

// Hero Selection
function selectHero(heroId, heroName) {
    const currentStep = drafterState.draftSequence[drafterState.phaseIndex];
    
    if (!currentStep) {
        alert('Draft is complete!');
        return;
    }
    
    const team = currentStep.team;
    const action = currentStep.action;
    
    // Check if hero already picked/banned
    const allPicks = [...drafterState.radiant.picks, ...drafterState.dire.picks];
    const allBans = [...drafterState.radiant.bans, ...drafterState.dire.bans];
    
    if (allPicks.includes(heroId) || allBans.includes(heroId)) {
        alert('This hero has already been picked or banned!');
        return;
    }
    
    // Add to appropriate team
    if (action === 'pick') {
        drafterState[team].picks.push(heroId);
        addHeroToPicks(team, heroId, heroName);
    } else {
        drafterState[team].bans.push(heroId);
        addHeroToBans(team, heroId, heroName);
    }
    
    // Mark hero as unavailable
    const heroCard = document.querySelector(`[data-hero-id="${heroId}"]`);
    if (heroCard) {
        heroCard.classList.add(action === 'pick' ? 'picked' : 'banned');
        heroCard.classList.add(team);
    }
    
    // Move to next phase
    drafterState.phaseIndex++;
    updatePhaseDisplay();
    updateAnalytics();
    updateWinProbability();
}

// Add hero to picks display
function addHeroToPicks(team, heroId, heroName) {
    const picksContainer = document.getElementById(`${team}Picks`);
    const emptySlot = picksContainer.querySelector('.pick-slot.empty');
    
    if (emptySlot) {
        const hero = heroes.find(h => h.id === heroId);
        const heroImage = `https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/${hero.name.replace('npc_dota_hero_', '')}.png`;
        
        emptySlot.classList.remove('empty');
        emptySlot.innerHTML = `
            <img src="${heroImage}" alt="${heroName}">
            <span class="hero-pick-name">${heroName}</span>
            <button class="remove-pick" onclick="removeHero('${team}', 'pick', ${heroId})">×</button>
        `;
    }
}

// Add hero to bans display
function addHeroToBans(team, heroId, heroName) {
    const bansContainer = document.getElementById(`${team}Bans`);
    const emptySlot = bansContainer.querySelector('.ban-slot.empty');
    
    if (emptySlot) {
        const hero = heroes.find(h => h.id === heroId);
        const heroImage = `https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/${hero.name.replace('npc_dota_hero_', '')}.png`;
        
        emptySlot.classList.remove('empty');
        emptySlot.innerHTML = `
            <img src="${heroImage}" alt="${heroName}" title="${heroName}">
            <button class="remove-ban" onclick="removeHero('${team}', 'ban', ${heroId})">×</button>
        `;
    }
}

// Remove hero from draft
function removeHero(team, action, heroId) {
    // Remove from state
    const index = drafterState[team][action + 's'].indexOf(heroId);
    if (index > -1) {
        drafterState[team][action + 's'].splice(index, 1);
    }
    
    // Remove visual indicator from hero pool
    const heroCard = document.querySelector(`[data-hero-id="${heroId}"]`);
    if (heroCard) {
        heroCard.classList.remove('picked', 'banned', 'radiant', 'dire');
    }
    
    // Go back one phase
    if (drafterState.phaseIndex > 0) {
        drafterState.phaseIndex--;
    }
    
    // Re-render
    renderDraft();
    updatePhaseDisplay();
    updateAnalytics();
}

// Update phase display
function updatePhaseDisplay() {
    const currentStep = drafterState.draftSequence[drafterState.phaseIndex];
    const phaseNameEl = document.getElementById('phaseName');
    const turnTextEl = document.getElementById('turnText');
    
    if (!currentStep) {
        phaseNameEl.textContent = 'Draft Complete';
        turnTextEl.textContent = 'All heroes selected';
        return;
    }
    
    const team = currentStep.team.charAt(0).toUpperCase() + currentStep.team.slice(1);
    const action = currentStep.action.charAt(0).toUpperCase() + currentStep.action.slice(1);
    
    // Determine phase name based on Captain's Mode 7.34 sequence
    let phaseName = 'Draft in Progress';
    if (drafterState.phaseIndex < 4) {
        phaseName = 'Initial Ban Phase';
    } else if (drafterState.phaseIndex < 10) {
        phaseName = 'First Pick Phase';
    } else if (drafterState.phaseIndex < 14) {
        phaseName = 'Second Ban Phase';
    } else if (drafterState.phaseIndex < 18) {
        phaseName = 'Final Pick Phase';
    } else {
        phaseName = 'Draft Complete';
    }
    
    phaseNameEl.textContent = phaseName;
    turnTextEl.textContent = `${team}'s Turn to ${action}`;
}

// Update analytics panels
function updateAnalytics() {
    updateSuggestions();
    updateCounters();
    updateSynergy();
    updateLanes();
}

// Update hero suggestions
function updateSuggestions() {
    const currentStep = drafterState.draftSequence[drafterState.phaseIndex];
    if (!currentStep || currentStep.action !== 'pick') {
        return;
    }
    
    const team = currentStep.team;
    const enemyTeam = team === 'radiant' ? 'dire' : 'radiant';
    const suggestionsEl = document.getElementById('recommendedHeroes');
    
    // Get unavailable heroes
    const unavailable = [...drafterState.radiant.picks, ...drafterState.dire.picks, 
                        ...drafterState.radiant.bans, ...drafterState.dire.bans];
    
    const available = heroes.filter(h => !unavailable.includes(h.id));
    
    // Get current team composition for synergy scoring
    const teamPicks = drafterState[team].picks;
    const teamHeroes = heroes.filter(h => teamPicks.includes(h.id));
    
    // Score each available hero
    const scored = available.map(hero => {
        const stats = heroStats.find(s => s.id === hero.id) || {};
        
        // Base score from hero stats
        let score = 50;
        
        // Win rate contribution (if available in stats)
        if (stats['1_win'] && stats['1_pick']) {
            const winRate = (stats['1_win'] / stats['1_pick']) * 100;
            score += (winRate - 50) * 0.5; // Normalize around 50%
        }
        
        // Pick rate contribution
        if (stats['1_pick']) {
            const pickRate = Math.min(stats['1_pick'] / 1000, 20); // Cap at 20
            score += pickRate * 0.3;
        }
        
        // Synergy bonus based on team composition
        if (teamHeroes.length > 0) {
            const primaryAttr = hero.primary_attr || 'str';
            const teamAttrs = teamHeroes.map(h => h.primary_attr || 'str');
            
            // Bonus for attribute diversity
            if (!teamAttrs.includes(primaryAttr)) {
                score += 5;
            }
            
            // Bonus for completing attribute triangle
            const uniqueAttrs = [...new Set([...teamAttrs, primaryAttr])];
            if (uniqueAttrs.length === 3) {
                score += 8;
            }
        }
        
        return { hero, score, stats };
    });
    
    // Sort by score and get top 10
    scored.sort((a, b) => b.score - a.score);
    const recommended = scored.slice(0, 10);
    
    if (recommended.length === 0) {
        suggestionsEl.innerHTML = '<p class="info-text">No heroes available</p>';
        return;
    }
    
    suggestionsEl.innerHTML = recommended.map(({ hero, score, stats }) => {
        const winRate = stats['1_win'] && stats['1_pick'] 
            ? ((stats['1_win'] / stats['1_pick']) * 100).toFixed(1)
            : '50.0';
        const pickRate = stats['1_pick'] 
            ? ((stats['1_pick'] / 10000) * 100).toFixed(1)
            : '5.0';
        const heroImage = `https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/${hero.name.replace('npc_dota_hero_', '')}.png`;
        
        return `
            <div class="suggestion-card" onclick="selectHero(${hero.id}, '${hero.localized_name}')">
                <img src="${heroImage}" alt="${hero.localized_name}">
                <div class="suggestion-info">
                    <strong>${hero.localized_name}</strong>
                    <div class="suggestion-stats">
                        <span class="win-rate">${winRate}% WR</span>
                        <span class="pick-rate">${pickRate}% Pick</span>
                    </div>
                </div>
                <div class="suggestion-score">${Math.round(score)}</div>
            </div>
        `;
    }).join('');
}

// Update counter picks
function updateCounters() {
    const currentStep = drafterState.draftSequence[drafterState.phaseIndex];
    if (!currentStep || currentStep.action !== 'pick') {
        return;
    }
    
    const team = currentStep.team;
    const enemyTeam = team === 'radiant' ? 'dire' : 'radiant';
    const countersEl = document.getElementById('counterPicks');
    
    if (drafterState[enemyTeam].picks.length === 0) {
        countersEl.innerHTML = '<p class="info-text">No enemy picks yet</p>';
        return;
    }
    
    // Mock counter data
    const unavailable = [...drafterState.radiant.picks, ...drafterState.dire.picks, 
                        ...drafterState.radiant.bans, ...drafterState.dire.bans];
    const available = heroes.filter(h => !unavailable.includes(h.id)).slice(0, 8);
    
    countersEl.innerHTML = available.map(hero => {
        const advantage = 50 + Math.random() * 10;
        const heroImage = `https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/${hero.name.replace('npc_dota_hero_', '')}.png`;
        
        return `
            <div class="counter-card" onclick="selectHero(${hero.id}, '${hero.localized_name}')">
                <img src="${heroImage}" alt="${hero.localized_name}">
                <div class="counter-info">
                    <strong>${hero.localized_name}</strong>
                    <div class="advantage-bar">
                        <div class="advantage-fill" style="width: ${advantage}%"></div>
                    </div>
                    <span class="advantage-text">${advantage.toFixed(1)}% Advantage</span>
                </div>
            </div>
        `;
    }).join('');
}

// Update synergy analysis
function updateSynergy() {
    const currentStep = drafterState.draftSequence[drafterState.phaseIndex];
    if (!currentStep) return;
    
    const team = currentStep.team;
    const teamPicks = drafterState[team].picks;
    
    const synergyScoreEl = document.getElementById('synergyScore');
    const synergyDetailsEl = document.getElementById('synergyDetails');
    
    if (teamPicks.length < 2) {
        synergyScoreEl.textContent = '0';
        synergyDetailsEl.innerHTML = '<p class="info-text">Need at least 2 heroes for synergy analysis</p>';
        return;
    }
    
    // Calculate synergy based on team composition
    const teamHeroes = heroes.filter(h => teamPicks.includes(h.id));
    
    // Attribute diversity
    const attrs = teamHeroes.map(h => h.primary_attr || 'str');
    const uniqueAttrs = new Set(attrs);
    const attrDiversity = (uniqueAttrs.size / 3) * 100; // 0-100 based on how many different attrs
    
    // Role coverage (based on hero attack type as proxy)
    const attackTypes = teamHeroes.map(h => h.attack_type || 'Melee');
    const meleeCount = attackTypes.filter(t => t === 'Melee').length;
    const rangedCount = attackTypes.filter(t => t === 'Ranged').length;
    const roleBalance = (1 - Math.abs(meleeCount - rangedCount) / teamHeroes.length) * 100;
    
    // Calculate overall synergy
    const synergyScore = Math.round((attrDiversity * 0.4) + (roleBalance * 0.6));
    synergyScoreEl.textContent = synergyScore;
    
    const synergyLevel = synergyScore > 75 ? 'Excellent' : synergyScore > 55 ? 'Good' : 'Needs Work';
    const synergyColor = synergyScore > 75 ? '#10b981' : synergyScore > 55 ? '#06b6d4' : '#f59e0b';
    
    document.querySelector('.score-circle').style.borderColor = synergyColor;
    document.querySelector('.score-circle').style.color = synergyColor;
    
    // Detailed breakdown
    const strCount = attrs.filter(a => a === 'str').length;
    const agiCount = attrs.filter(a => a === 'agi').length;
    const intCount = attrs.filter(a => a === 'int').length;
    
    synergyDetailsEl.innerHTML = `
        <div class="synergy-rating">${synergyLevel} Team Composition</div>
        <div class="synergy-breakdown">
            <div class="synergy-item">
                <i class="fas fa-users"></i>
                <span>Composition: <strong>${strCount} STR / ${agiCount} AGI / ${intCount} INT</strong></span>
            </div>
            <div class="synergy-item">
                <i class="fas fa-shield-alt"></i>
                <span>Melee/Ranged: <strong>${meleeCount} Melee / ${rangedCount} Ranged</strong></span>
            </div>
            <div class="synergy-item">
                <i class="fas fa-chart-line"></i>
                <span>Attribute Diversity: <strong>${Math.round(attrDiversity)}%</strong></span>
            </div>
            <div class="synergy-item">
                <i class="fas fa-balance-scale"></i>
                <span>Role Balance: <strong>${Math.round(roleBalance)}%</strong></span>
            </div>
        </div>
    `;
}

// Update lane matchups
function updateLanes() {
    const lanesEl = document.getElementById('laneMatchups');
    
    if (drafterState.radiant.picks.length === 0 && drafterState.dire.picks.length === 0) {
        lanesEl.innerHTML = '<p class="info-text">Pick heroes to see lane predictions</p>';
        return;
    }
    
    const lanes = ['Safe Lane', 'Mid Lane', 'Off Lane'];
    lanesEl.innerHTML = lanes.map(lane => {
        const advantage = -20 + Math.random() * 40;
        const favoredTeam = advantage > 0 ? 'Radiant' : 'Dire';
        const absAdvantage = Math.abs(advantage);
        const barWidth = Math.min(absAdvantage * 2, 100);
        const barColor = advantage > 0 ? '#10b981' : '#ef4444';
        
        return `
            <div class="lane-matchup">
                <div class="lane-name">${lane}</div>
                <div class="lane-bar-container">
                    <span class="team-label radiant">Radiant</span>
                    <div class="lane-bar">
                        <div class="lane-advantage" style="width: ${barWidth}%; background: ${barColor}; 
                             ${advantage > 0 ? 'left' : 'right'}: 50%;"></div>
                    </div>
                    <span class="team-label dire">Dire</span>
                </div>
                <div class="lane-prediction">${favoredTeam} favored (+${absAdvantage.toFixed(0)}%)</div>
            </div>
        `;
    }).join('');
}

// Update win probability
function updateWinProbability() {
    const radiantPicks = drafterState.radiant.picks;
    const direPicks = drafterState.dire.picks;
    
    if (radiantPicks.length === 0 && direPicks.length === 0) {
        document.getElementById('radiantWinProb').textContent = '50%';
        document.getElementById('direWinProb').textContent = '50%';
        return;
    }
    
    // Calculate based on hero win rates
    const radiantHeroes = heroes.filter(h => radiantPicks.includes(h.id));
    const direHeroes = heroes.filter(h => direPicks.includes(h.id));
    
    // Calculate average win rate for each team
    let radiantAvgWR = 50;
    let direAvgWR = 50;
    
    if (radiantHeroes.length > 0) {
        const radiantWRs = radiantHeroes.map(h => {
            const stats = heroStats.find(s => s.id === h.id);
            if (stats && stats['1_win'] && stats['1_pick']) {
                return (stats['1_win'] / stats['1_pick']) * 100;
            }
            return 50;
        });
        radiantAvgWR = radiantWRs.reduce((a, b) => a + b, 0) / radiantWRs.length;
    }
    
    if (direHeroes.length > 0) {
        const direWRs = direHeroes.map(h => {
            const stats = heroStats.find(s => s.id === h.id);
            if (stats && stats['1_win'] && stats['1_pick']) {
                return (stats['1_win'] / stats['1_pick']) * 100;
            }
            return 50;
        });
        direAvgWR = direWRs.reduce((a, b) => a + b, 0) / direWRs.length;
    }
    
    // Normalize to sum to 100%
    const total = radiantAvgWR + direAvgWR;
    const radiantProb = (radiantAvgWR / total) * 100;
    const direProb = (direAvgWR / total) * 100;
    
    document.getElementById('radiantWinProb').textContent = radiantProb.toFixed(1) + '%';
    document.getElementById('direWinProb').textContent = direProb.toFixed(1) + '%';
}

// Re-render entire draft
function renderDraft() {
    // Clear all slots
    document.querySelectorAll('.pick-slot').forEach(slot => {
        slot.classList.add('empty');
        slot.innerHTML = '<span class="position-label">' + slot.dataset.position + '</span>';
    });
    
    document.querySelectorAll('.ban-slot').forEach(slot => {
        slot.classList.add('empty');
        slot.innerHTML = '';
    });
    
    // Re-add picks
    drafterState.radiant.picks.forEach(heroId => {
        const hero = heroes.find(h => h.id === heroId);
        if (hero) addHeroToPicks('radiant', heroId, hero.localized_name);
    });
    
    drafterState.dire.picks.forEach(heroId => {
        const hero = heroes.find(h => h.id === heroId);
        if (hero) addHeroToPicks('dire', heroId, hero.localized_name);
    });
    
    // Re-add bans
    drafterState.radiant.bans.forEach(heroId => {
        const hero = heroes.find(h => h.id === heroId);
        if (hero) addHeroToBans('radiant', heroId, hero.localized_name);
    });
    
    drafterState.dire.bans.forEach(heroId => {
        const hero = heroes.find(h => h.id === heroId);
        if (hero) addHeroToBans('dire', heroId, hero.localized_name);
    });
}

// Reset draft
function resetDraft() {
    if (!confirm('Are you sure you want to reset the draft?')) return;
    
    drafterState.radiant = { picks: [], bans: [] };
    drafterState.dire = { picks: [], bans: [] };
    drafterState.phaseIndex = 0;
    
    // Clear hero pool markers
    document.querySelectorAll('.hero-pool-card').forEach(card => {
        card.classList.remove('picked', 'banned', 'radiant', 'dire');
    });
    
    renderDraft();
    updatePhaseDisplay();
    updateAnalytics();
    updateWinProbability();
}

// Swap sides
function swapSides() {
    const tempPicks = drafterState.radiant.picks;
    const tempBans = drafterState.radiant.bans;
    
    drafterState.radiant.picks = drafterState.dire.picks;
    drafterState.radiant.bans = drafterState.dire.bans;
    drafterState.dire.picks = tempPicks;
    drafterState.dire.bans = tempBans;
    
    renderDraft();
    updateWinProbability();
}

// Initialize filters
function initializeFilters() {
    // Search
    const searchInput = document.getElementById('heroSearch');
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.hero-pool-card').forEach(card => {
            const name = card.dataset.heroName.toLowerCase();
            card.style.display = name.includes(query) ? 'block' : 'none';
        });
    });
    
    // Attribute filters
    document.querySelectorAll('.attr-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const attr = btn.dataset.attr;
            
            // Update active button
            document.querySelectorAll('.attr-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Filter heroes
            document.querySelectorAll('.hero-pool-card').forEach(card => {
                if (attr === 'all' || card.dataset.attr === attr) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// Initialize tabs
function initializeTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.dataset.tab;
            
            // Update active tab button
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Show corresponding panel
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
        });
    });
}
