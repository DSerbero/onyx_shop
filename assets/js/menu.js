document.getElementById("btn_menu").addEventListener("click", () => {
    document.getElementById("sidebar").classList.add("active");
    document.getElementById("overlay").classList.add("active");
});

document.getElementById("overlay").addEventListener("click", () => {
    document.getElementById("sidebar").classList.remove("active");
    document.getElementById("overlay").classList.remove("active");
});