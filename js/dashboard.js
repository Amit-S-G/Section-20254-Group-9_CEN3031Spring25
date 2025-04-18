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

document.addEventListener('DOMContentLoaded', function () {
    // Restore main scroll
    const savedScrollY = localStorage.getItem('scrollY');
    if (savedScrollY !== null) {
        window.scrollTo(0, parseInt(savedScrollY));
        localStorage.removeItem('scrollY');
    }

    // Restore scroll for task sections
    const taskSection = document.querySelector('.tasks-section');
    const expiredTaskSection = document.querySelector('.expired-tasks-section');

    const savedTaskScrollY = localStorage.getItem('taskScrollY');
    if (savedTaskScrollY !== null && taskSection) {
        taskSection.scrollTop = parseInt(savedTaskScrollY);
        localStorage.removeItem('taskScrollY');
    }

    const savedExpiredScrollY = localStorage.getItem('expiredScrollY');
    if (savedExpiredScrollY !== null && expiredTaskSection) {
        expiredTaskSection.scrollTop = parseInt(savedExpiredScrollY);
        localStorage.removeItem('expiredScrollY');
    }

    // Save scroll before leaving
    const saveScroll = () => {
        localStorage.setItem('scrollY', window.scrollY);
        if (taskSection) {
            localStorage.setItem('taskScrollY', taskSection.scrollTop);
        }
        if (expiredTaskSection) {
            localStorage.setItem('expiredScrollY', expiredTaskSection.scrollTop);
        }
    };

    // Attach saveScroll to key elements
    document.querySelectorAll('a.delete-button, .clear-tasks a, input[type="checkbox"]').forEach(el => {
        el.addEventListener('click', saveScroll);
    });

    const form = document.querySelector('.add-task-section form');
    if (form) {
        form.addEventListener('submit', saveScroll);
    }
});