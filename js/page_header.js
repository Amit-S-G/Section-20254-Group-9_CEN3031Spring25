//Hamburger
function toggleMenu() {
    var menu = document.getElementById("menu");
    menu.classList.toggle("show");
}

// Toggle Audio Mute and Update Speaker Icon
function toggleMute() {
    const audioElement = document.getElementById('sound');
    const icon = document.getElementById('muteIcon');
    
    // Toggle the muted property
    audioElement.muted = !audioElement.muted;
    
    // If unmuted and audio is paused, attempt to play it.
    if (!audioElement.muted && audioElement.paused) {
        const play = audioElement.play();
        if (play !== undefined) {
            play.catch(error => {
                console.error("Error playing audio:", error);
            });
        }
    }
    
    // Update the speaker icon based on the muted state.
    if (audioElement.muted) {
        icon.src = "../img/icons/sound_icons/speaker_muted.png";
    } else {
        icon.src = "../img/icons/sound_icons/speaker.png";
    }
}