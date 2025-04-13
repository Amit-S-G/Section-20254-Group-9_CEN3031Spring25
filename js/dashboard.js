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

