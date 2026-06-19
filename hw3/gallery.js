function upDate(element) {
    document.getElementById("image").innerHTML = element.alt;
    document.getElementById("image").style.backgroundImage = "url('" + element.src + "')";

}

function unDo() {
    document.getElementById("image").innerHTML = "Hover over an image below";
    document.getElementById("image").style.backgroundImage = "none";

}

window.addEventListener('load', (event) => {
    console.log('Page fully loaded');
    const imgs = document.querySelectorAll('.preview');
    imgs.forEach(element => {
        element.tabIndex = 0;
    });

});