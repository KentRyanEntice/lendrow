window.onload = function () {
    const navigation = document.querySelector(".navigation");
    const menu = document.querySelector("#btn");

    menu.addEventListener("click", function () {
        navigation.classList.toggle("open");
        menuChange();
    })

    function menuChange() {
        if (navigation.classList.contains("open")) {
            menu.classList.toggle("active");
        } else {
            menu.classList.toggle("active");
        }
    }

    let list = document.querySelectorAll('.navigation li');

    function activelink() {
        list.forEach((item) => {
            item.classList.remove('active');
        });

        let currentFileName = window.location.href.split('/').pop();
        currentFileName = currentFileName.split('?')[0];

        list.forEach((item) => {
            if (item.id === currentFileName.replace('.php', '')) {
                item.classList.add('active');
            }
        });
    }

    list.forEach((item) => {
        item.addEventListener('click', activelink);
    });

    activelink();
}