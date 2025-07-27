// Global variables
let currentRadioStation = null;
let currentTVChannel = null;
let currentPodcastEpisode = null;
let mainPlayer = null; // Main YouTube Player
let tvPlayerInstance = null; // TV YouTube Player Instance

// DOM elements
const radioPlayer = document.getElementById('radio-player');
const tvPlayer = document.getElementById('tv-player');
const tvYouTubePlayer = document.getElementById('tv-youtube-player');
const podcastPlayer = document.getElementById('podcast-player');
const playPauseBtn = document.getElementById('play-pause-btn');
const stopBtn = document.getElementById('stop-btn');
const volumeSlider = document.getElementById('volume-slider');

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeAudioControls();
    initializeRadioFilters();
    initializeViewToggle();
    initializeScheduleTabs();
    loadRadioStations();
    loadTVChannels();
    loadPodcasts();
    loadNews();
    loadVideos(); // Load Videos
    
    // Load home page content
    loadHomeContent();
    
    // Initialize YouTube API
    window.onYouTubeIframeAPIReady = function() {
        initializeYouTubePlayers();
    };
    
    // Show home section by default
    showSection('home');
});

// Navigation functionality
function initializeNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('href').substring(1);
            showSection(targetSection);
            
            // Update active nav link
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Show specific section
function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => section.classList.remove('active'));
    
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update navigation active state
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + sectionId) {
            link.classList.add('active');
        }
    });
}

// Initialize audio controls
function initializeAudioControls() {
    // Play/Pause button
    playPauseBtn.addEventListener('click', function() {
        if (currentRadioStation && radioPlayer.src) {
            if (radioPlayer.paused) {
                radioPlayer.play();
                this.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                radioPlayer.pause();
                this.innerHTML = '<i class="fas fa-play"></i>';
            }
        }
    });
    
    // Stop button
    stopBtn.addEventListener('click', function() {
        if (radioPlayer.src) {
            radioPlayer.pause();
            radioPlayer.currentTime = 0;
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
    });
    
    // Volume control
    volumeSlider.addEventListener('input', function() {
        const volume = this.value / 100;
        if (radioPlayer) radioPlayer.volume = volume;
        if (tvPlayer) tvPlayer.volume = volume;
        if (podcastPlayer) podcastPlayer.volume = volume;
    });
    
    // Set initial volume
    const initialVolume = volumeSlider.value / 100;
    if (radioPlayer) radioPlayer.volume = initialVolume;
    if (tvPlayer) tvPlayer.volume = initialVolume;
    if (podcastPlayer) podcastPlayer.volume = initialVolume;
}

// Load radio stations from backend
async function loadRadioStations() {
    try {
        const response = await fetch('backend/api/radio.php');
        const stations = await response.json();
        
        const stationsContainer = document.getElementById('radio-stations');
        stationsContainer.innerHTML = '';
        
        stations.forEach(station => {
            const stationCard = createRadioStationCard(station);
            stationsContainer.appendChild(stationCard);
        });
        
        // Load today's schedule
        loadRadioSchedule();
        
    } catch (error) {
        console.error('Error loading radio stations:', error);
        document.getElementById('radio-stations').innerHTML = '<div class="loading">Error loading radio stations</div>';
    }
}

// Create radio station card
function createRadioStationCard(station) {
    const card = document.createElement('div');
    card.className = 'station-card';
    card.innerHTML = `
        <div class="card-header">
            <div class="card-logo">
                <i class="fas fa-radio"></i>
            </div>
            <div class="card-info">
                <h3>${station.name}</h3>
                <p>${station.category}</p>
            </div>
            <div class="card-play-btn">
                <button class="play-btn" onclick="playRadioStation(${station.id}, '${station.stream_url}', '${station.name}', event)">
                    <i class="fas fa-play"></i>
                </button>
            </div>
        </div>
        <div class="card-content">
            <p>${station.description}</p>
            <span class="card-category">${station.category}</span>
        </div>
    `;
    
    card.addEventListener('click', () => selectRadioStation(station, card));
    return card;
}

// Select radio station
function selectRadioStation(station, cardElement) {
    // Remove active class from all cards
    document.querySelectorAll('.station-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to selected card
    cardElement.classList.add('active');
    
    // Update current station info
    currentRadioStation = station;
    document.getElementById('current-station-name').textContent = station.name;
    document.getElementById('current-station-desc').textContent = station.description;
    
    // Set up audio player
    radioPlayer.src = station.stream_url;
    radioPlayer.style.display = 'block';
    
    // Enable controls
    playPauseBtn.disabled = false;
    stopBtn.disabled = false;
    playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
}

// Load radio schedule
async function loadRadioSchedule() {
    try {
        const response = await fetch('backend/api/schedule.php');
        const schedules = await response.json();
        
        const scheduleContainer = document.getElementById('radio-schedule');
        scheduleContainer.innerHTML = '';
        
        schedules.forEach(schedule => {
            const scheduleItem = document.createElement('div');
            scheduleItem.className = 'schedule-item';
            scheduleItem.innerHTML = `
                <div class="schedule-time">${schedule.start_time} - ${schedule.end_time}</div>
                <div class="schedule-show">${schedule.show_name}</div>
                <div class="schedule-host">Host: ${schedule.host_name || 'N/A'}</div>
            `;
            scheduleContainer.appendChild(scheduleItem);
        });
        
    } catch (error) {
        console.error('Error loading schedule:', error);
        document.getElementById('radio-schedule').innerHTML = '<div class="loading">Error loading schedule</div>';
    }
}

// Load TV channels
async function loadTVChannels() {
    try {
        const response = await fetch('backend/api/tv_streams.php');
        const channels = await response.json();
        
        const channelsContainer = document.getElementById('tv-channels');
        channelsContainer.innerHTML = '';
        
        channels.forEach(channel => {
            const channelCard = createTVChannelCard(channel);
            channelsContainer.appendChild(channelCard);
        });
        
    } catch (error) {
        console.error('Error loading TV channels:', error);
        document.getElementById('tv-channels').innerHTML = '<div class="loading">Error loading TV channels</div>';
    }
}

// Create TV channel card
function createTVChannelCard(channel) {
    const card = document.createElement('div');
    card.className = 'channel-card';
    card.innerHTML = `
        <div class="card-header">
            <div class="card-logo">
                <i class="fas fa-tv"></i>
            </div>
            <div class="card-info">
                <h3>${channel.channel_name}</h3>
                <p>${channel.category}</p>
            </div>
        </div>
        <div class="card-content">
            <p>${channel.description}</p>
            <span class="card-category">${channel.category}</span>
            ${channel.is_live ? '<span class="card-category" style="background-color: #ff6b6b; margin-left: 0.5rem;">LIVE</span>' : ''}
        </div>
    `;
    
    card.addEventListener('click', () => selectTVChannel(channel, card));
    return card;
}

// Select TV channel using YouTube
function selectTVChannel(channel, cardElement) {
    // Remove active class from all cards
    document.querySelectorAll('.channel-card').forEach(card => card.classList.remove('active'));
    
    // Add active class to selected card
    cardElement.classList.add('active');
    
    // Update current channel
    currentTVChannel = channel;
    
    // Set up YouTube player for TV
    if (tvPlayerInstance) {
        tvPlayerInstance.loadVideoById(channel.youtube_id);
    } else {
        initializeTVPlayer(channel.youtube_id);
    }
    
    // Update TV info
    document.getElementById('tv-youtube-player').style.display = 'block';
    document.querySelector('.tv-placeholder').style.display = 'none';
    const tvInfo = document.getElementById('tv-info');
    if (tvInfo) {
        tvInfo.style.display = 'block';
        document.getElementById('current-tv-title').textContent = channel.channel_name;
        document.getElementById('current-tv-channel').textContent = channel.broadcaster;
        document.getElementById('current-tv-category').textContent = channel.category;
        document.getElementById('current-tv-description').textContent = channel.description;
    }
}

// Load podcasts
async function loadPodcasts() {
    try {
        const response = await fetch('backend/api/podcasts.php');
        const podcasts = await response.json();
        
        const podcastsContainer = document.getElementById('podcasts-list');
        podcastsContainer.innerHTML = '';
        
        podcasts.forEach(podcast => {
            const podcastCard = createPodcastCard(podcast);
            podcastsContainer.appendChild(podcastCard);
        });
        
    } catch (error) {
        console.error('Error loading podcasts:', error);
        document.getElementById('podcasts-list').innerHTML = '<div class="loading">Error loading podcasts</div>';
    }
}

// Create podcast card with episodes
function createPodcastCard(podcast) {
    const card = document.createElement('div');
    card.className = 'podcast-card';
    card.innerHTML = `
        <div class="card-header">
            <div class="card-logo">
                <i class="fas fa-podcast"></i>
            </div>
            <div class="card-info">
                <h3>${podcast.title}</h3>
                <p>Host: ${podcast.host_name || 'Unknown'}</p>
            </div>
        </div>
        <div class="card-content">
            <p>${podcast.description}</p>
            <span class="card-category">${podcast.category}</span>
            <div class="podcast-episodes" id="episodes-${podcast.id}">
                <div class="loading-episodes">Loading episodes...</div>
            </div>
        </div>
    `;
    
    // Load episodes for this podcast
    loadPodcastEpisodesInCard(podcast, card);
    return card;
}

// Load podcast episodes
async function loadPodcastEpisodes(podcast) {
    try {
        const response = await fetch(`backend/api/episodes.php?podcast_id=${podcast.id}`);
        const episodes = await response.json();
        
        if (episodes.length > 0) {
            // Play the latest episode
            const latestEpisode = episodes[0];
            selectPodcastEpisode(latestEpisode, podcast);
        }
        
    } catch (error) {
        console.error('Error loading podcast episodes:', error);
    }
}

// Select podcast episode
function selectPodcastEpisode(episode, podcast) {
    currentPodcastEpisode = episode;
    
    // Update episode info
    document.getElementById('current-episode-title').textContent = episode.title;
    document.getElementById('current-podcast-name').textContent = podcast.title;
    
    // Set up audio player
    podcastPlayer.src = episode.audio_url;
    podcastPlayer.style.display = 'block';
}

// Load news articles
async function loadNews() {
    try {
        const response = await fetch('backend/api/news.php');
        const articles = await response.json();
        
        const newsContainer = document.getElementById('news-articles');
        newsContainer.innerHTML = '';
        
        // Limit to latest 5 news articles
        articles.slice(0, 5).forEach(article => {
            const newsCard = createNewsCard(article);
            newsContainer.appendChild(newsCard);
        });
        
    } catch (error) {
        console.error('Error loading news:', error);
        document.getElementById('news-articles').innerHTML = '<div class="loading">Error loading news</div>';
    }
}

// Create news card
function createNewsCard(article) {
    const card = document.createElement('div');
    card.className = 'news-card';
    
    const publishedDate = new Date(article.published_at || article.created_at);
    const formattedDate = publishedDate.toLocaleDateString();
    
    card.innerHTML = `
        <div class="card-header">
            <div class="card-logo">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="card-info">
                <h3>${article.title}</h3>
                <p>By ${article.author || 'Staff Writer'}</p>
            </div>
        </div>
        <div class="card-content">
            <p>${article.summary || article.content.substring(0, 150) + '...'}</p>
            <div class="news-meta">
                <span class="card-category">${article.category}</span>
                <span class="news-date">${formattedDate}</span>
            </div>
        </div>
    `;
    
    return card;
}

// Mobile menu toggle (for future mobile responsiveness)
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    navMenu.classList.toggle('active');
}

// Load home page content
async function loadHomeContent() {
    console.log('🔄 Loading home page content...');
    
    try {
        // Load radio stations for home page (limit to 6)
        console.log('📻 Loading radio stations...');
        const radioResponse = await fetch('backend/api/radio.php');
        console.log('Radio response status:', radioResponse.status);
        
        if (!radioResponse.ok) {
            throw new Error(`Radio API error: ${radioResponse.status}`);
        }
        
        const radioStations = await radioResponse.json();
        console.log('Radio stations loaded:', radioStations.length);
        
        if (radioStations && radioStations.length > 0) {
            // Load all radio stations for horizontal scrolling (up to 12 for better scrolling experience)
            loadHomeRadioStations(radioStations.slice(0, 12));
        } else {
            document.getElementById('home-radio-stations').innerHTML = '<div class="loading">No radio stations found. Please run setup_stations.php first.</div>';
        }
        
        // Load TV channels for home page (centered - only 3 channels)
        console.log('📺 Loading TV channels...');
        const tvResponse = await fetch('backend/api/tv_streams.php');
        if (tvResponse.ok) {
            const tvChannels = await tvResponse.json();
            console.log('TV channels loaded:', tvChannels.length);
            if (tvChannels && tvChannels.length > 0) {
                // Select top 3 channels for centered homepage display
                loadHomeTVChannels(tvChannels.slice(0, 3));
            } else {
                document.getElementById('home-tv-channels').innerHTML = '<div class="loading">No TV channels found.</div>';
            }
        }
        
        // Load podcasts for home page
        console.log('🎧 Loading podcasts...');
        const podcastsResponse = await fetch('backend/api/podcasts.php');
        if (podcastsResponse.ok) {
            const podcasts = await podcastsResponse.json();
            console.log('Podcasts loaded:', podcasts.length);
            if (podcasts && podcasts.length > 0) {
                loadHomePodcasts(podcasts.slice(0, 6));
            } else {
                document.getElementById('home-podcasts').innerHTML = '<div class="loading">No podcasts found.</div>';
            }
        }
        
        // Load news for home page
        console.log('📰 Loading news...');
        const newsResponse = await fetch('backend/api/news.php');
        if (newsResponse.ok) {
            const newsArticles = await newsResponse.json();
            console.log('News articles loaded:', newsArticles.length);
            if (newsArticles && newsArticles.length > 0) {
                loadHomeNews(newsArticles.slice(0, 5));
            } else {
                document.getElementById('home-news').innerHTML = '<div class="loading">No news articles found.</div>';
            }
        }
        
        console.log('✅ Home content loading complete!');
        
    } catch (error) {
        console.error('❌ Error loading home content:', error);
        // Show error message to user
        const errorMsg = '<div class="loading" style="color: red;">Error loading content. Please check console or run setup_stations.php</div>';
        document.getElementById('home-radio-stations').innerHTML = errorMsg;
        document.getElementById('home-tv-channels').innerHTML = errorMsg;
        document.getElementById('home-podcasts').innerHTML = errorMsg;
        document.getElementById('home-news').innerHTML = errorMsg;
    }
}

// Load radio stations for home page
function loadHomeRadioStations(stations) {
    const container = document.getElementById('home-radio-stations');
    container.innerHTML = '';
    
    stations.forEach(station => {
        const card = createHomeCard(station, 'radio', station.name, station.category, station.description);
        card.addEventListener('click', () => {
            showSection('radio');
            setTimeout(() => {
                const stationCard = Array.from(document.querySelectorAll('.station-card')).find(card => 
                    card.querySelector('h3').textContent === station.name
                );
                if (stationCard) {
                    selectRadioStation(station, stationCard);
                }
            }, 100);
        });
        container.appendChild(card);
    });
    
    // Note: Homepage uses grid layout, not horizontal scroll
    // initializeRadioScroll() is only for pages with .radio-cards-wrapper
}

// Load TV channels for home page
function loadHomeTVChannels(channels) {
    const container = document.getElementById('home-tv-channels');
    container.innerHTML = '';
    
    channels.forEach(channel => {
        const card = createHomeCard(channel, 'tv', channel.channel_name, channel.category, channel.description);
        card.addEventListener('click', () => {
            showSection('tv');
            setTimeout(() => {
                const channelCard = Array.from(document.querySelectorAll('.channel-card')).find(card => 
                    card.querySelector('h3').textContent === channel.channel_name
                );
                if (channelCard) {
                    selectTVChannel(channel, channelCard);
                }
            }, 100);
        });
        container.appendChild(card);
    });
}

// Load podcasts for home page
function loadHomePodcasts(podcasts) {
    const container = document.getElementById('home-podcasts');
    container.innerHTML = '';
    
    podcasts.forEach(podcast => {
        const card = createHomeCard(podcast, 'podcast', podcast.title, podcast.category, podcast.description);
        card.addEventListener('click', () => {
            showSection('podcasts');
            setTimeout(() => {
                loadPodcastEpisodes(podcast);
            }, 100);
        });
        container.appendChild(card);
    });
}

// Load news for home page
function loadHomeNews(articles) {
    const container = document.getElementById('home-news');
    container.innerHTML = '';
    
    articles.forEach(article => {
        const card = createHomeCard(article, 'newspaper', article.title, article.category, article.summary || article.content.substring(0, 100) + '...');
        card.addEventListener('click', () => {
            showSection('news');
        });
        container.appendChild(card);
    });
}

// Create home card (smaller version)
function createHomeCard(item, iconType, title, category, description) {
    const card = document.createElement('div');
    card.className = 'home-card';
    
    const iconClass = iconType === 'tv' ? 'fas fa-tv' : 
                     iconType === 'podcast' ? 'fas fa-podcast' :
                     iconType === 'newspaper' ? 'fas fa-newspaper' : 'fas fa-radio';
    
    card.innerHTML = `
        <div class="card-header">
            <div class="card-logo">
                <i class="${iconClass}"></i>
            </div>
            <div class="card-info">
                <h3>${title}</h3>
                <p>${category}</p>
            </div>
        </div>
        <div class="card-content">
            <p>${description}</p>
            <span class="card-category">${category}</span>
        </div>
    `;
    
    return card;
}

// Play radio station function
function playRadioStation(stationId, streamUrl, stationName, event) {
    event.stopPropagation(); // Prevent card click
    
    // Stop any currently playing audio
    if (radioPlayer) radioPlayer.pause();
    if (podcastPlayer) podcastPlayer.pause();
    
    // Update all play buttons to play state
    document.querySelectorAll('.play-btn').forEach(btn => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        btn.classList.remove('playing');
    });
    
    // Set up radio player
    radioPlayer.src = streamUrl;
    radioPlayer.load();
    
    // Update button to playing state
    const button = event.target.closest('.play-btn');
    button.innerHTML = '<i class="fas fa-pause"></i>';
    button.classList.add('playing');
    
    // Play the stream
    radioPlayer.play().then(() => {
        console.log(`Playing radio station: ${stationName}`);
    }).catch(error => {
        console.error('Error playing radio stream:', error);
        button.innerHTML = '<i class="fas fa-play"></i>';
        button.classList.remove('playing');
        alert('Error playing radio stream. Please try again.');
    });
    
    // Update global controls
    if (playPauseBtn) {
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        playPauseBtn.disabled = false;
    }
    if (stopBtn) {
        stopBtn.disabled = false;
    }
}

// Load podcast episodes in card
async function loadPodcastEpisodesInCard(podcast, card) {
    try {
        const response = await fetch(`backend/api/episodes.php?podcast_id=${podcast.id}`);
        const episodes = await response.json();
        
        const episodesContainer = card.querySelector(`#episodes-${podcast.id}`);
        
        if (episodes && episodes.length > 0) {
            episodesContainer.innerHTML = '';
            
            // Show first 3 episodes with play buttons
            episodes.slice(0, 3).forEach(episode => {
                const episodeItem = document.createElement('div');
                episodeItem.className = 'episode-item';
                episodeItem.innerHTML = `
                    <div class="episode-info">
                        <h4>${episode.title}</h4>
                        <p>${episode.duration || 'Unknown duration'}</p>
                    </div>
                    <button class="play-btn episode-play-btn" onclick="playPodcastEpisode(${episode.id}, '${episode.audio_url}', '${episode.title}', event)">
                        <i class="fas fa-play"></i>
                    </button>
                `;
                episodesContainer.appendChild(episodeItem);
            });
            
            if (episodes.length > 3) {
                const moreEpisodes = document.createElement('div');
                moreEpisodes.className = 'more-episodes';
                moreEpisodes.innerHTML = `<small>+${episodes.length - 3} more episodes</small>`;
                episodesContainer.appendChild(moreEpisodes);
            }
        } else {
            episodesContainer.innerHTML = '<div class="no-episodes">No episodes available</div>';
        }
        
    } catch (error) {
        console.error('Error loading podcast episodes:', error);
        const episodesContainer = card.querySelector(`#episodes-${podcast.id}`);
        episodesContainer.innerHTML = '<div class="error-episodes">Error loading episodes</div>';
    }
}

// Play podcast episode function
function playPodcastEpisode(episodeId, audioUrl, episodeTitle, event) {
    event.stopPropagation();
    
    // Stop any currently playing audio
    if (radioPlayer) radioPlayer.pause();
    if (podcastPlayer) podcastPlayer.pause();
    
    // Update all play buttons
    document.querySelectorAll('.play-btn').forEach(btn => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        btn.classList.remove('playing');
    });
    
    // Set up podcast player
    podcastPlayer.src = audioUrl;
    podcastPlayer.load();
    
    // Update button
    const button = event.target.closest('.play-btn');
    button.innerHTML = '<i class="fas fa-pause"></i>';
    button.classList.add('playing');
    
    // Play the episode
    podcastPlayer.play().then(() => {
        console.log(`Playing podcast episode: ${episodeTitle}`);
    }).catch(error => {
        console.error('Error playing podcast:', error);
        button.innerHTML = '<i class="fas fa-play"></i>';
        button.classList.remove('playing');
        alert('Error playing podcast. Please try again.');
    });
}

// Create beautiful round radio card for homepage
function createRadioCard(station) {
    const card = document.createElement('div');
    card.className = 'radio-card';
    
    // Create unique colors based on station name
    const colors = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
        'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
        'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)'
    ];
    
    const colorIndex = station.id % colors.length;
    const cardColor = colors[colorIndex];
    
    // Generate initials from station name
    const initials = station.name
        .split(' ')
        .map(word => word.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
    
    card.innerHTML = `
        <div class="card-logo" style="background: ${cardColor}">
            ${station.logo_url ? `<img src="${station.logo_url}" alt="${station.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">` : `<span style="font-size: 1.5rem; font-weight: bold;">${initials}</span>`}
        </div>
        <div class="card-info">${station.name}</div>
        <button class="play-overlay" onclick="playRadioStationFromCard(${station.id}, '${station.stream_url}', '${station.name}', event)">
            <i class="fas fa-play"></i>
        </button>
    `;
    
    // Add click handler to navigate to radio section
    card.addEventListener('click', (e) => {
        if (!e.target.closest('.play-overlay')) {
            showSection('radio');
            setTimeout(() => {
                const stationCard = Array.from(document.querySelectorAll('.station-card')).find(card => 
                    card.querySelector('h3').textContent === station.name
                );
                if (stationCard) {
                    selectRadioStation(station, stationCard);
                }
            }, 100);
        }
    });
    
    return card;
}

// Play radio station from card
function playRadioStationFromCard(stationId, streamUrl, stationName, event) {
    event.stopPropagation();
    
    // Stop any currently playing audio
    if (radioPlayer) radioPlayer.pause();
    if (podcastPlayer) podcastPlayer.pause();
    
    // Update all play overlays
    document.querySelectorAll('.play-overlay').forEach(btn => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        btn.classList.remove('playing');
    });
    
    // Set up radio player
    radioPlayer.src = streamUrl;
    radioPlayer.load();
    
    // Update button to playing state
    const button = event.target.closest('.play-overlay');
    button.innerHTML = '<i class="fas fa-pause"></i>';
    button.classList.add('playing');
    
    // Play the stream
    radioPlayer.play().then(() => {
        console.log(`Playing radio station: ${stationName}`);
    }).catch(error => {
        console.error('Error playing radio stream:', error);
        button.innerHTML = '<i class="fas fa-play"></i>';
        button.classList.remove('playing');
        alert('Error playing radio stream. Please try again.');
    });
    
    // Update global controls if available
    if (playPauseBtn) {
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        playPauseBtn.disabled = false;
    }
    if (stopBtn) {
        stopBtn.disabled = false;
    }
}

// Initialize radio scroll functionality
function initializeRadioScroll() {
    const scrollContainer = document.querySelector('.radio-cards-wrapper');
    const leftBtn = document.getElementById('radio-scroll-left');
    const rightBtn = document.getElementById('radio-scroll-right');
    
    if (!scrollContainer || !leftBtn || !rightBtn) return;
    
    // Calculate scroll amount based on card width + gap
    const cardWidth = 150; // radio card width
    const gap = 24; // 1.5rem gap converted to px
    const scrollAmount = (cardWidth + gap) * 2; // Scroll 2 cards at a time
    
    // Scroll left
    leftBtn.addEventListener('click', () => {
        scrollContainer.scrollBy({ 
            left: -scrollAmount, 
            behavior: 'smooth' 
        });
    });
    
    // Scroll right  
    rightBtn.addEventListener('click', () => {
        scrollContainer.scrollBy({ 
            left: scrollAmount, 
            behavior: 'smooth' 
        });
    });
    
    // Update scroll button visibility
    function updateScrollButtons() {
        const { scrollLeft, scrollWidth, clientWidth } = scrollContainer;
        
        // Show/hide left button
        if (scrollLeft <= 5) {
            leftBtn.style.opacity = '0.3';
            leftBtn.style.pointerEvents = 'none';
        } else {
            leftBtn.style.opacity = '1';
            leftBtn.style.pointerEvents = 'auto';
        }
        
        // Show/hide right button
        if (scrollLeft >= scrollWidth - clientWidth - 5) {
            rightBtn.style.opacity = '0.3';
            rightBtn.style.pointerEvents = 'none';
        } else {
            rightBtn.style.opacity = '1';
            rightBtn.style.pointerEvents = 'auto';
        }
    }
    
    // Update on scroll
    scrollContainer.addEventListener('scroll', updateScrollButtons);
    
    // Update on load and resize
    setTimeout(updateScrollButtons, 500);
    window.addEventListener('resize', () => {
        setTimeout(updateScrollButtons, 100);
    });
    
    // Add touch/swipe support for mobile
    let isDown = false;
    let startX;
    let scrollLeftStart;
    
    scrollContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        scrollContainer.style.cursor = 'grabbing';
        startX = e.pageX - scrollContainer.offsetLeft;
        scrollLeftStart = scrollContainer.scrollLeft;
        e.preventDefault();
    });
    
    scrollContainer.addEventListener('mouseleave', () => {
        isDown = false;
        scrollContainer.style.cursor = 'grab';
    });
    
    scrollContainer.addEventListener('mouseup', () => {
        isDown = false;
        scrollContainer.style.cursor = 'grab';
        updateScrollButtons();
    });
    
    scrollContainer.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 1.5;
        scrollContainer.scrollLeft = scrollLeftStart - walk;
    });
    
    // Set initial cursor
    scrollContainer.style.cursor = 'grab';
    
    // Add keyboard navigation
    scrollContainer.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            scrollContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            scrollContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    });
    
    // Make container focusable for keyboard navigation
    scrollContainer.setAttribute('tabindex', '0');
}

// Error handling for media elements
if (radioPlayer) {
    radioPlayer.addEventListener('error', function() {
        console.error('Error loading radio stream');
        alert('Error loading radio stream. Please try another station.');
    });
}

if (tvPlayer) {
    tvPlayer.addEventListener('error', function() {
        console.error('Error loading TV stream');
        alert('Error loading TV stream. Please try another channel.');
    });
}

if (podcastPlayer) {
    podcastPlayer.addEventListener('error', function() {
        console.error('Error loading podcast episode');
        alert('Error loading podcast episode. Please try another episode.');
    });
}

// YouTube API Functions

// Initialize YouTube Players for Videos and TV
function initializeYouTubePlayers() {
    console.log('Initializing YouTube players...');
    
    // Initialize players after DOM is ready
    setTimeout(() => {
        if (document.getElementById('main-youtube-player')) {
            mainPlayer = new YT.Player('main-youtube-player', {
                height: '390',
                width: '640',
                events: {
                    'onReady': () => {
                        console.log('Main YouTube player is ready');
                    }
                }
            });
        }
        
        if (document.getElementById('tv-youtube-player')) {
            tvPlayerInstance = new YT.Player('tv-youtube-player', {
                height: '390',
                width: '640',
                videoId: '', // No initial video
                events: {
                    'onReady': () => {
                        console.log('TV YouTube player is ready');
                    }
                }
            });
        }
    }, 1000);
}

// Initialize TV Player specifically
function initializeTVPlayer(videoId) {
    if (document.getElementById('tv-youtube-player')) {
        tvPlayerInstance = new YT.Player('tv-youtube-player', {
            height: '390',
            width: '640',
            videoId: videoId,
            events: {
                'onReady': () => {
                    tvPlayerInstance.playVideo();
                }
            }
        });
    }
}

// Load videos
async function loadVideos() {
    try {
        const response = await fetch('backend/api/videos.php');
        const videos = await response.json();
        
        const videosContainer = document.getElementById('videos-list');
        if (videosContainer) {
            videosContainer.innerHTML = '';
            
            // Limit to latest 5 videos
            videos.slice(0, 5).forEach(video => {
                const videoCard = createVideoCard(video);
                videosContainer.appendChild(videoCard);
            });
        }
        
        // Load videos for home page
        loadHomeVideos(videos.slice(0, 5));
        
    } catch (error) {
        console.error('Error loading videos:', error);
        const videosContainer = document.getElementById('videos-list');
        if (videosContainer) {
            videosContainer.innerHTML = '<div class="loading">Error loading videos</div>';
        }
    }
}

// Create video card
function createVideoCard(video) {
    const card = document.createElement('div');
    card.className = 'video-card';
    card.innerHTML = `
        <div class="video-thumbnail">
            <img src="${video.thumbnail_url}" alt="${video.title}">
            <div class="video-play-btn">
                <i class="fas fa-play"></i>
            </div>
        </div>
        <div class="video-info">
            <h3>${video.title}</h3>
            <p class="video-channel">${video.channel_name}</p>
            <p class="video-description">${video.description}</p>
            <div class="video-meta">
                <span class="video-views">${video.views_count} views</span>
                <span class="video-duration">${video.duration || 'N/A'}</span>
            </div>
        </div>
    `;

    card.addEventListener('click', () => openVideoPlayer(video));
    return card;
}

// Open video player
function openVideoPlayer(video) {
    const playerContainer = document.getElementById('video-player-container');
    if (playerContainer && mainPlayer) {
        mainPlayer.loadVideoById(video.youtube_id);
        playerContainer.style.display = 'block';
        document.getElementById('current-video-title').textContent = video.title;
        document.getElementById('current-video-channel').textContent = video.channel_name;
        document.getElementById('current-video-description').textContent = video.description;
        document.getElementById('current-video-views').textContent = `${video.views_count} views`;
        document.getElementById('current-video-duration').textContent = video.duration;
    }
}

// Close video player
function closeVideoPlayer() {
    const playerContainer = document.getElementById('video-player-container');
    if (playerContainer) {
        playerContainer.style.display = 'none';
        if (mainPlayer) {
            mainPlayer.stopVideo();
        }
    }
}

// Load videos for home page
function loadHomeVideos(videos) {
    const container = document.getElementById('home-videos');
    if (container) {
        container.innerHTML = '';
        
        videos.forEach(video => {
            const card = createHomeCard(video, 'play-circle', video.title, video.category, video.description);
            card.addEventListener('click', () => {
                showSection('videos');
                setTimeout(() => {
                    openVideoPlayer(video);
                }, 100);
            });
            container.appendChild(card);
        });
    }
}

// Video filter functionality
document.addEventListener('DOMContentLoaded', () => {
    // Initialize video filters
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            e.target.classList.add('active');
            
            const category = e.target.dataset.category;
            filterVideosByCategory(category);
        });
    });
    
    // Initialize video search
    const searchBtn = document.getElementById('video-search-btn');
    const searchInput = document.getElementById('video-search-input');
    
    if (searchBtn) {
        searchBtn.addEventListener('click', searchVideos);
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchVideos();
            }
        });
    }
});

function filterVideosByCategory(category) {
    const allVideos = Array.from(document.querySelectorAll('.video-card'));

    allVideos.forEach(videoCard => {
        const videoChannel = videoCard.querySelector('.video-channel').textContent;
        const videoDescription = videoCard.querySelector('.video-description').textContent;
        
        if (category === 'all' || videoChannel.includes(category) || videoDescription.includes(category)) {
            videoCard.style.display = 'block';
        } else {
            videoCard.style.display = 'none';
        }
    });
}

// Video search functionality
function searchVideos() {
    const query = document.getElementById('video-search-input').value.toLowerCase();
    const allVideos = Array.from(document.querySelectorAll('.video-card'));

    allVideos.forEach(videoCard => {
        const title = videoCard.querySelector('h3').textContent.toLowerCase();
        const description = videoCard.querySelector('.video-description').textContent.toLowerCase();
        const channel = videoCard.querySelector('.video-channel').textContent.toLowerCase();
        
        if (title.includes(query) || description.includes(query) || channel.includes(query)) {
            videoCard.style.display = 'block';
        } else {
            videoCard.style.display = 'none';
        }
    });
}

// Enhanced Radio Features

// Initialize radio filters
function initializeRadioFilters() {
    const filterBtns = document.querySelectorAll('.radio-filters .filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            e.target.classList.add('active');
            
            const category = e.target.dataset.category;
            filterRadioStations(category);
        });
    });
}

// Filter radio stations by category
function filterRadioStations(category) {
    const allStations = Array.from(document.querySelectorAll('.station-card'));

    allStations.forEach(stationCard => {
        const stationCategory = stationCard.querySelector('.card-info p').textContent;
        const stationName = stationCard.querySelector('.card-info h3').textContent;
        
        if (category === 'all' || 
            stationCategory.toLowerCase().includes(category.toLowerCase()) ||
            stationName.toLowerCase().includes(category.toLowerCase())) {
            stationCard.style.display = 'block';
        } else {
            stationCard.style.display = 'none';
        }
    });
}

// Initialize view toggle
function initializeViewToggle() {
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const stationsContainer = document.getElementById('radio-stations');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Remove active class from all buttons
            toggleBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            e.target.classList.add('active');
            
            const view = e.target.dataset.view;
            toggleStationsView(view, stationsContainer);
        });
    });
}

// Toggle stations view between grid and list
function toggleStationsView(view, container) {
    if (view === 'grid') {
        container.classList.remove('list-view');
        container.classList.add('grid-view');
    } else {
        container.classList.remove('grid-view');
        container.classList.add('list-view');
    }
}

// Initialize schedule tabs
function initializeScheduleTabs() {
    const scheduleTabs = document.querySelectorAll('.schedule-tab');
    scheduleTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            // Remove active class from all tabs
            scheduleTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            e.target.classList.add('active');
            
            const timeFilter = e.target.dataset.time;
            loadScheduleByTime(timeFilter);
        });
    });
}

// Load schedule by time filter
async function loadScheduleByTime(timeFilter) {
    try {
        const response = await fetch(`backend/api/schedule.php?time=${timeFilter}`);
        const schedules = await response.json();
        
        const scheduleContainer = document.getElementById('radio-schedule');
        scheduleContainer.innerHTML = '';
        
        if (schedules && schedules.length > 0) {
            schedules.forEach(schedule => {
                const scheduleItem = document.createElement('div');
                scheduleItem.className = 'schedule-item';
                
                const isNowPlaying = timeFilter === 'now' && schedule.is_current;
                if (isNowPlaying) {
                    scheduleItem.classList.add('current-show');
                }
                
                scheduleItem.innerHTML = `
                    <div class="schedule-time">${schedule.start_time} - ${schedule.end_time}</div>
                    <div class="schedule-show">${schedule.show_name} ${isNowPlaying ? '<span class="live-indicator">• LIVE</span>' : ''}</div>
                    <div class="schedule-host">Host: ${schedule.host_name || 'N/A'}</div>
                    ${schedule.description ? `<div class="schedule-description">${schedule.description}</div>` : ''}
                `;
                scheduleContainer.appendChild(scheduleItem);
            });
        } else {
            scheduleContainer.innerHTML = '<div class="loading">No schedule available for this time period.</div>';
        }
        
    } catch (error) {
        console.error('Error loading schedule:', error);
        document.getElementById('radio-schedule').innerHTML = '<div class="loading">Error loading schedule</div>';
    }
}

// Enhanced volume control with visual feedback
function initializeVolumeControl() {
    const volumeSlider = document.getElementById('volume-slider');
    const volumeValue = document.querySelector('.volume-value');
    
    if (volumeSlider && volumeValue) {
        volumeSlider.addEventListener('input', function() {
            const volume = this.value;
            volumeValue.textContent = volume + '%';
            
            // Update all audio elements
            if (radioPlayer) radioPlayer.volume = volume / 100;
            if (tvPlayer) tvPlayer.volume = volume / 100;
            if (podcastPlayer) podcastPlayer.volume = volume / 100;
            
            // Update volume icon
            const volumeIcon = document.querySelector('.volume-control i');
            if (volumeIcon) {
                if (volume == 0) {
                    volumeIcon.className = 'fas fa-volume-mute';
                } else if (volume < 50) {
                    volumeIcon.className = 'fas fa-volume-down';
                } else {
                    volumeIcon.className = 'fas fa-volume-up';
                }
            }
        });
    }
}

// Load featured stations
async function loadFeaturedStations() {
    try {
        const response = await fetch('backend/api/radio.php?featured=true');
        const stations = await response.json();
        
        const featuredContainer = document.getElementById('featured-stations');
        if (featuredContainer && stations && stations.length > 0) {
            featuredContainer.innerHTML = '';
            
            // Show up to 4 featured stations
            stations.slice(0, 4).forEach(station => {
                const featuredCard = createFeaturedStationCard(station);
                featuredContainer.appendChild(featuredCard);
            });
        }
        
    } catch (error) {
        console.error('Error loading featured stations:', error);
    }
}

// Create featured station card
function createFeaturedStationCard(station) {
    const card = document.createElement('div');
    card.className = 'featured-station-card';
    card.innerHTML = `
        <div class="featured-card-bg"></div>
        <div class="featured-card-content">
            <div class="featured-logo">
                <i class="fas fa-star"></i>
            </div>
            <h4>${station.name}</h4>
            <p>${station.category}</p>
            <button class="featured-play-btn" onclick="playRadioStation(${station.id}, '${station.stream_url}', '${station.name}', event)">
                <i class="fas fa-play"></i>
            </button>
        </div>
    `;
    
    return card;
}

// Enhanced station selection with animation
function selectRadioStationEnhanced(station, cardElement) {
    // Remove active class from all cards with animation
    document.querySelectorAll('.station-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to selected card
    cardElement.classList.add('active');
    
    // Update current station info with animation
    const stationName = document.getElementById('current-station-name');
    const stationDesc = document.getElementById('current-station-desc');
    const stationIcon = document.getElementById('current-station-icon');
    
    // Fade out and update content
    stationName.style.opacity = '0';
    stationDesc.style.opacity = '0';
    
    setTimeout(() => {
        stationName.textContent = station.name;
        stationDesc.textContent = station.description;
        
        // Update icon if available
        if (station.logo_url) {
            stationIcon.innerHTML = `<img src="${station.logo_url}" alt="${station.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
        } else {
            stationIcon.innerHTML = '<i class="fas fa-radio"></i>';
        }
        
        // Fade back in
        stationName.style.opacity = '1';
        stationDesc.style.opacity = '1';
    }, 200);
    
    // Update current station
    currentRadioStation = station;
    
    // Set up audio player
    radioPlayer.src = station.stream_url;
    
    // Enable controls
    playPauseBtn.disabled = false;
    stopBtn.disabled = false;
    playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
}
