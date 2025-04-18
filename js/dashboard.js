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

    // ✅ Restore scroll positions (if stored)
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

    // ✅ Intercept checkbox task completion/incompletion
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

    // ✅ Intercept delete task buttons
    const deleteLinks = document.querySelectorAll('a[href*="delete_task_id"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // Allow the confirm dialog to show first
            const confirmed = confirm('Delete this task?');
            if (!confirmed) {
                e.preventDefault();
                return;
            }

            e.preventDefault(); // Prevent default redirect
            const href = this.href;
            saveScrollState();
            setTimeout(() => {
                window.location.href = href;
            }, 20);
        });
    });

    // ✅ Intercept Clear All Tasks link
    const clearLink = document.querySelector('a[href*="clear_tasks"]');
    if (clearLink) {
        clearLink.addEventListener('click', function (e) {
            const confirmed = confirm('Are you sure you want to clear all tasks?');
            if (!confirmed) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            saveScrollState();
            const href = this.href;
            setTimeout(() => {
                window.location.href = href;
            }, 20);
        });
    }

    // ✅ Save scroll state on Add Task form submit
    const addTaskForm = document.querySelector('form[action*="dashboard.php"]');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function () {
            saveScrollState();
        });
    }
});