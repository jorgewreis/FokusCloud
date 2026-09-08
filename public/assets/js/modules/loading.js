function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function pointLoading() {
    const pointLoading = document.querySelector('.point-loading');
    let wordLoading = document.querySelector('.word-loading');
    let wordPrimary = document.querySelector('.word-loading .primary');
    let wordSecondary = document.querySelector('.word-loading .secondary');

    // POINT MOVE
    if (pointLoading) {
        await sleep(400); // Simulate a delay
        pointLoading.classList.replace('offscreen', 'onscreen');
        await sleep(200); // Simulate a delay
        pointLoading.classList.add('blink');
        await sleep(2500); // Simulate a delay
        pointLoading.classList.remove('visible');
        pointLoading.classList.remove('blink');
    }

    // WORD SHOW
    if (wordLoading) {
        wordLoading.classList.add('visible');
        await sleep(400); // Simulate a delay
        wordPrimary.textContent = 'FO';
        await sleep(400); // Simulate a delay
        wordPrimary.textContent = 'FOK';
        await sleep(400);
        wordPrimary.textContent = 'FOKU';
        await sleep(400);
        wordPrimary.textContent = 'FOKUS';
        await sleep(800);
        wordSecondary.textContent = 'CLOUD';
        await sleep(1000);
        wordLoading.classList.add('top');
    }
}