// Progress Bar Animation
let number = document.getElementById("number");
let counter = 0;
let targetPercentage = parseInt(number.getAttribute("data-target"));
let interval = setInterval(() => {
    if (counter >= targetPercentage) {
        clearInterval(interval);
    } else {
        counter += 1;
        number.innerHTML = counter + "%";
    }
}, 18);

// Toggle Audio Mute and Update Speaker Icon
function toggleMute() {
    const audioElement = document.getElementById('sound');
    const icon = document.getElementById('muteIcon');
    
    // Toggle the muted property
    audioElement.muted = !audioElement.muted;
    
    // If unmuted and audio is paused, attempt to play it.
    if (!audioElement.muted && audioElement.paused) {
        const playPromise = audioElement.play();
        if (playPromise !== undefined) {
            playPromise.catch(error => {
                console.error("Error playing audio:", error);
            });
        }
    }
    
    // Update the speaker icon based on the muted state.
    if (audioElement.muted) {
        icon.src = "../img/speaker_muted.png";
    } else {
        icon.src = "../img/speaker.png";
    }
}

// Toggle the Hamburger Menu Visibility
function toggleMenu() {
    var menu = document.getElementById("menu");
    menu.classList.toggle("show");
}