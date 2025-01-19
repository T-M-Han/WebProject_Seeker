document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');

    menuToggle.addEventListener('click', function () {
        this.classList.toggle('open');
        nav.classList.toggle('open');
    });
});

function handleSearch() {
    var searchBox = document.getElementById('searchBox');
    if (searchBox.style.display === 'none' || searchBox.style.display === '') {
        searchBox.style.display = 'block';
    } else {
        searchBox.style.display = 'none';
    }
}

document.getElementById('searchIcon').addEventListener('click', function(event) {
    event.preventDefault();
    handleSearch();
});

document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var query = document.querySelector('#searchBox input[name="query"]').value.trim();
    if (query !== '') {
        window.location.href = '../3main/9search.php?query=' + encodeURIComponent(query);
    }
});
