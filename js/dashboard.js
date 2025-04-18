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
    const taskSection = document.querySelector('.tasks-section');

    // ✅ Restore scroll only if set
    const savedScrollY = localStorage.getItem('restoreScrollY');
    if (savedScrollY !== null) {
        window.scrollTo(0, parseInt(savedScrollY));
        localStorage.removeItem('restoreScrollY');
    }

    const savedTaskScroll = localStorage.getItem('restoreTaskScrollY');
    if (savedTaskScroll && taskSection) {
        taskSection.scrollTop = parseInt(savedTaskScroll);
        localStorage.removeItem('restoreTaskScrollY');
    }

    function saveScrollState() {
        localStorage.setItem('restoreScrollY', window.scrollY);
        if (taskSection) {
            localStorage.setItem('restoreTaskScrollY', taskSection.scrollTop);
        }
    }

    // ✅ Intercept task checkboxes (complete/incomplete)
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function (e) {
            e.preventDefault();
            const link = this.getAttribute('onclick').match(/window\.location\.href='([^']+)'/)[1];
            saveScrollState();
            setTimeout(() => {
                window.location.href = link;
            }, 20);
        });
    });

    // ✅ Add Task form submission (button click or enter)
    const addTaskForm = document.querySelector('form[action*="dashboard.php"]');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function () {
            saveScrollState();
        });
    }
});