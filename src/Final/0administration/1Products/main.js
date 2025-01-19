document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');

    menuToggle.addEventListener('click', function () {
        this.classList.toggle('open');
        nav.classList.toggle('open');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-details');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sneakerId = this.getAttribute('data-sneakerid');
            const detailsContainer = document.getElementById('sneakerDetailsContainer_' + sneakerId);
            const detailsRow = document.getElementById('sneakerDetailsRow_' + sneakerId);

            toggleButtons.forEach(otherButton => {
                const otherSneakerId = otherButton.getAttribute('data-sneakerid');
                if (otherSneakerId !== sneakerId) {
                    const otherDetailsRow = document.getElementById('sneakerDetailsRow_' + otherSneakerId);
                    if (otherDetailsRow.style.display !== 'none') {
                        otherDetailsRow.style.display = 'none';
                    }
                }
            });

            if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                fetch('get_sneaker_details.php?sneakerid=' + sneakerId)
                    .then(response => response.text())
                    .then(data => {
                        detailsContainer.innerHTML = data;
                        detailsRow.style.display = 'table-row';
                    })
                    .catch(error => {
                        console.error('Error fetching sneaker details:', error);
                    });
            } else {
                detailsRow.style.display = 'none';
            }
        });
    });
});


function checkBrand() {
    var brandSelect = document.getElementById("brand");
    var newBrandInput = document.getElementById("newBrandInput");

    if (brandSelect.value === "new") {
        newBrandInput.style.display = "block";
        newBrandInput.required = true;
    } else {
        newBrandInput.style.display = "none";
        newBrandInput.required = false;
    }
}

function openPopupForm() {
    document.getElementById('popupFormContainer').style.display = 'block';
}

function closePopupForm() {
    document.getElementById('popupFormContainer').style.display = 'none';
    window.location.reload();
}
