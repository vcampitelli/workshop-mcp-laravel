document.querySelectorAll("img.insert-svg").forEach(($img) => {
    fetch($img.src).then((response) => {
        if (!response.ok) {
            return;
        }
        response.text().then((contents) => {
            const $wrapper = document.createElement("div");
            $wrapper.classList.add("svg-wrapper");
            if ($img.dataset.id) {
                contents = contents.replace(
                    "<svg",
                    '<svg data-id="' + encodeURIComponent($img.dataset.id) + '"',
                );
            }
            $wrapper.innerHTML = contents.replace('<?xml version="1.0" encoding="UTF-8"?>', "");
            if ($img.classList.contains('w-100')) {
                $wrapper.firstElementChild.style.width = '100%';
                $wrapper.firstElementChild.removeAttribute('height');
            }
            $img.parentNode.replaceChild($wrapper, $img);
        });
    });
});
Reveal.initialize({
    hash: true,
    history: true,
    mouseWheel: false,
    overview: false,
    slideNumber: "c/t",
    plugins: [RevealHighlight],
});

/**
 https://codepen.io/anon/embed/YzeOLYe
 */
let index = 0,
    interval = 1000;

const rand = (min, max) =>
  Math.floor(Math.random() * (max - min + 1)) + min;

const animate = star => {
  star.style.setProperty("--star-left", `${rand(-10, 100)}%`);
  star.style.setProperty("--star-top", `${rand(-40, 80)}%`);

  star.style.animation = "none";
  star.offsetHeight;
  star.style.animation = "";
}

for(const star of document.getElementsByClassName("magic-star")) {
  setTimeout(() => {
    animate(star);

    setInterval(() => animate(star), 3000);
  }, index++ * (interval / 3))
}
