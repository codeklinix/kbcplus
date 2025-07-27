// Admin Dashboard JavaScript

// Global variables
let currentEditId = null;
let currentEditType = null;

// Initialize admin dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    checkAuth();
    
    // Initialize form handlers
    initializeFormHandlers();
    
    // Load initial data
    loadAllData();
});

// Check if user is authenticated
function checkAuth() {
    // Simple authentication check - in production, use proper JWT or session validation
    const isLoggedIn = localStorage.getItem('admin_logged_in');
    if (!isLoggedIn) {
        window.location.href = 'login.html';
        return;
    }
    
    // Display user info
    const username = localStorage.getItem('admin_username') || 'Admin';
    document.getElementById('user-info').innerHTML = `<p>Welcome, <strong>${username}</strong></p>`;
}

// Initialize all form handlers
function initializeFormHandlers() {
    // Radio form
    document.getElementById('radio-form').addEventListener('submit', handleRadioSubmit);
    
    // TV form
    document.getElementById('tv-form').addEventListener('submit', handleTVSubmit);
    
    // Podcast form
    document.getElementById('podcast-form').addEventListener('submit', handlePodcastSubmit);
    
    // News form
    document.getElementById('news-form').addEventListener('submit', handleNewsSubmit);
    
    // Schedule form
    document.getElementById('schedule-form').addEventListener('submit', handleScheduleSubmit);
    
    // User form
    document.getElementById('user-form').addEventListener('submit', handleUserSubmit);
    
    // Settings form
    document.getElementById('settings-form').addEventListener('submit', handleSettingsSubmit);
}

// Tab switching functionality
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Load data for the selected tab
    loadTabData(tabName);
}

// Load data for specific tab
function loadTabData(tabName) {
    switch(tabName) {
        case 'radio':
            loadRadioStations();
            break;
        case 'tv':
            loadTVChannels();
            break;
        case 'podcasts':
            loadPodcasts();
            break;
        case 'news':
            loadNews();
            break;
        case 'schedule':
            loadSchedule();
            break;
        case 'users':
            loadUsers();
            break;
    }
}

// Load all initial data
function loadAllData() {
    loadRadioStations();
    // Other data will be loaded when tabs are clicked
}

// Radio Stations Functions
async function handleRadioSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = currentEditId ? 
            `backend/api/manage_radio.php?action=update&id=${currentEditId}` : 
            'backend/api/manage_radio.php?action=add';
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', currentEditId ? 'Radio station updated successfully!' : 'Radio station added successfully!');
            clearForm('radio-form');
            loadRadioStations();
            resetEditMode();
        } else {
            showMessage('error', result.error || 'Failed to save radio station');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

async function loadRadioStations() {
    try {
        const response = await fetch('backend/api/radio.php');
        const stations = await response.json();
        
        const container = document.getElementById('radio-items');
        container.innerHTML = '';
        
        stations.forEach(station => {
            const card = createItemCard('radio', station);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading radio stations:', error);
        document.getElementById('radio-items').innerHTML = '<div class="error">Error loading radio stations</div>';
    }
}

// TV Channels Functions
async function handleTVSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = currentEditId ? 
            `backend/api/manage_tv.php?action=update&id=${currentEditId}` : 
            'backend/api/manage_tv.php?action=add';
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', currentEditId ? 'TV channel updated successfully!' : 'TV channel added successfully!');
            clearForm('tv-form');
            loadTVChannels();
            resetEditMode();
        } else {
            showMessage('error', result.error || 'Failed to save TV channel');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

async function loadTVChannels() {
    try {
        const response = await fetch('backend/api/tv.php');
        const channels = await response.json();
        
        const container = document.getElementById('tv-items');
        container.innerHTML = '';
        
        channels.forEach(channel => {
            const card = createItemCard('tv', channel);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading TV channels:', error);
        document.getElementById('tv-items').innerHTML = '<div class="error">Error loading TV channels</div>';
    }
}

// Podcast Functions
async function handlePodcastSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = currentEditId ? 
            `backend/api/manage_podcasts.php?action=update&id=${currentEditId}` : 
            'backend/api/manage_podcasts.php?action=add';
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', currentEditId ? 'Podcast updated successfully!' : 'Podcast added successfully!');
            clearForm('podcast-form');
            loadPodcasts();
            resetEditMode();
        } else {
            showMessage('error', result.error || 'Failed to save podcast');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

async function loadPodcasts() {
    try {
        const response = await fetch('backend/api/podcasts.php');
        const podcasts = await response.json();
        
        const container = document.getElementById('podcast-items');
        container.innerHTML = '';
        
        podcasts.forEach(podcast => {
            const card = createItemCard('podcast', podcast);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading podcasts:', error);
        document.getElementById('podcast-items').innerHTML = '<div class="error">Error loading podcasts</div>';
    }
}

// News Functions
async function handleNewsSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = currentEditId ? 
            `backend/api/manage_news.php?action=update&id=${currentEditId}` : 
            'backend/api/manage_news.php?action=add';
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', currentEditId ? 'News article updated successfully!' : 'News article added successfully!');
            clearForm('news-form');
            loadNews();
            resetEditMode();
        } else {
            showMessage('error', result.error || 'Failed to save news article');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

async function loadNews() {
    try {
        const response = await fetch('backend/api/news.php');
        const news = await response.json();
        
        const container = document.getElementById('news-items');
        container.innerHTML = '';
        
        news.forEach(article => {
            const card = createItemCard('news', article);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading news:', error);
        document.getElementById('news-items').innerHTML = '<div class="error">Error loading news</div>';
    }
}

// Schedule Functions
async function handleScheduleSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = currentEditId ? 
            `backend/api/manage_schedule.php?action=update&id=${currentEditId}` : 
            'backend/api/manage_schedule.php?action=add';
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', currentEditId ? 'Schedule updated successfully!' : 'Schedule added successfully!');
            clearForm('schedule-form');
            loadSchedule();
            resetEditMode();
        } else {
            showMessage('error', result.error || 'Failed to save schedule');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

async function loadSchedule() {
    try {
        const response = await fetch('backend/api/schedule.php');
        const schedules = await response.json();
        
        const container = document.getElementById('schedule-items');
        container.innerHTML = '';
        
        schedules.forEach(schedule => {
            const card = createItemCard('schedule', schedule);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading schedule:', error);
        document.getElementById('schedule-items').innerHTML = '<div class="error">Error loading schedule</div>';
    }
}

// User Functions
async function handleUserSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const url = currentEditId ? 
            `backend/api/manage_users.php?action=update&id=${currentEditId}` : 
            'backend/api/manage_users.php?action=add';
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', currentEditId ? 'User updated successfully!' : 'User added successfully!');
            clearForm('user-form');
            loadUsers();
            resetEditMode();
        } else {
            showMessage('error', result.error || 'Failed to save user');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

async function loadUsers() {
    try {
        const response = await fetch('backend/api/manage_users.php?action=list');
        const users = await response.json();
        
        const container = document.getElementById('user-items');
        container.innerHTML = '';
        
        users.forEach(user => {
            const card = createItemCard('user', user);
            container.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('user-items').innerHTML = '<div class="error">Error loading users</div>';
    }
}

// Settings Functions
async function handleSettingsSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('backend/api/manage_settings.php?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', 'Settings updated successfully!');
        } else {
            showMessage('error', result.error || 'Failed to save settings');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

// Generic item card creator
function createItemCard(type, item) {
    const card = document.createElement('div');
    card.className = 'item-card';
    
    let statusClass = '';
    let statusText = '';
    
    // Determine status
    if (item.is_active !== undefined) {
        statusClass = item.is_active ? 'status-active' : 'status-inactive';
        statusText = item.is_active ? 'Active' : 'Inactive';
    } else if (item.status !== undefined) {
        statusClass = item.status === 'published' || item.status == 1 ? 'status-active' : 'status-inactive';
        statusText = item.status === 'published' || item.status == 1 ? 'Active' : 'Inactive';
    }
    
    let cardContent = '';
    
    switch(type) {
        case 'radio':
            cardContent = `
                <h4>${item.name}</h4>
                <p><strong>URL:</strong> ${item.stream_url || item.url}</p>
                <p><strong>Category:</strong> ${item.category}</p>
                <p><strong>Description:</strong> ${item.description || 'No description'}</p>
                <p><span class="${statusClass}"></span>${statusText}</p>
            `;
            break;
        case 'tv':
            cardContent = `
                <h4>${item.name}</h4>
                <p><strong>URL:</strong> ${item.stream_url || item.url}</p>
                <p><strong>Category:</strong> ${item.category}</p>
                <p><strong>Description:</strong> ${item.description || 'No description'}</p>
                <p><span class="${statusClass}"></span>${statusText}</p>
            `;
            break;
        case 'podcast':
            cardContent = `
                <h4>${item.title}</h4>
                <p><strong>Host:</strong> ${item.host || 'Unknown'}</p>
                <p><strong>Duration:</strong> ${item.duration || 'N/A'} minutes</p>
                <p><strong>Category:</strong> ${item.category}</p>
                <p><span class="${statusClass}"></span>${statusText}</p>
            `;
            break;
        case 'news':
            cardContent = `
                <h4>${item.title}</h4>
                <p><strong>Author:</strong> ${item.author || 'Unknown'}</p>
                <p><strong>Category:</strong> ${item.category}</p>
                <p><strong>Published:</strong> ${item.created_at || item.published_at || 'Unknown'}</p>
                <p><span class="${statusClass}"></span>${statusText}</p>
            `;
            break;
        case 'schedule':
            cardContent = `
                <h4>${item.title || item.show_name}</h4>
                <p><strong>Type:</strong> ${item.type || 'Radio'}</p>
                <p><strong>Time:</strong> ${item.start_time} - ${item.end_time}</p>
                <p><strong>Host:</strong> ${item.host_name || 'N/A'}</p>
                <p><span class="${statusClass}"></span>${statusText}</p>
            `;
            break;
        case 'user':
            cardContent = `
                <h4>${item.username}</h4>
                <p><strong>Email:</strong> ${item.email}</p>
                <p><strong>Role:</strong> ${item.role}</p>
                <p><strong>Created:</strong> ${item.created_at || 'Unknown'}</p>
            `;
            break;
    }
    
    card.innerHTML = `
        ${cardContent}
        <div class="item-actions">
            <button class="btn" onclick="editItem('${type}', ${item.id})">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-danger" onclick="deleteItem('${type}', ${item.id})">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    `;
    
    return card;
}

// Edit item functionality
async function editItem(type, id) {
    try {
        const response = await fetch(`backend/api/manage_${type}.php?action=get&id=${id}`);
        const item = await response.json();
        
        if (item.success) {
            currentEditId = id;
            currentEditType = type;
            
            // Populate form based on type
            populateForm(type, item.data);
            
            // Update button text
            const form = document.getElementById(`${type}-form`);
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update ' + type.charAt(0).toUpperCase() + type.slice(1);
        } else {
            showMessage('error', 'Failed to load item for editing');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

// Populate form with item data
function populateForm(type, data) {
    const form = document.getElementById(`${type}-form`);
    
    Object.keys(data).forEach(key => {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) {
            input.value = data[key];
        }
    });
}

// Delete item functionality
async function deleteItem(type, id) {
    if (!confirm(`Are you sure you want to delete this ${type}?`)) {
        return;
    }
    
    try {
        const response = await fetch(`backend/api/manage_${type}.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('success', `${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully!`);
            loadTabData(type);
        } else {
            showMessage('error', result.error || `Failed to delete ${type}`);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Network error occurred');
    }
}

// Reset edit mode
function resetEditMode() {
    currentEditId = null;
    currentEditType = null;
    
    // Reset all submit buttons
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            const formId = form.id.replace('-form', '');
            submitBtn.innerHTML = `<i class="fas fa-plus"></i> Add ${formId.charAt(0).toUpperCase() + formId.slice(1)}`;
        }
    });
}

// Clear form
function clearForm(formId) {
    const form = document.getElementById(formId);
    form.reset();
    resetEditMode();
}

// Show message
function showMessage(type, message) {
    const container = document.getElementById('message-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    container.innerHTML = '';
    container.appendChild(messageDiv);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 5000);
}

// Logout functionality
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('admin_logged_in');
        localStorage.removeItem('admin_username');
        window.location.href = 'login.html';
    }
}
