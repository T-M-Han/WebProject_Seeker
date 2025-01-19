document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');

    menuToggle.addEventListener('click', function () {
        this.classList.toggle('open');
        nav.classList.toggle('open');
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(addToCartForm);
            
            fetch('addtocart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                window.location.reload();
            });
        });
    } else {
        console.error('Add to cart form not found');
    }
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
