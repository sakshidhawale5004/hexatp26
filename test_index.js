const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const match = html.match(/<script>\s*\(function buildTicker\(\) \{([\s\S]*?)\}\)\(\);\s*<\/script>/);
if (match) {
    try {
        const JSDOM = require('jsdom').JSDOM;
        const dom = new JSDOM(`
            <div class="compliance-ticker-wrap">
                <div id="tickerContent1"></div>
                <div id="tickerContent2"></div>
            </div>
            <script>(function buildTicker() {${match[1]}})();</script>
        `, { runScripts: 'dangerously' });
        
        setTimeout(() => {
            const items = dom.window.document.querySelectorAll('#tickerContent1 .ticker-item');
            console.log('Number of items in tickerContent1:', items.length);
            if (items.length > 0) {
                console.log('First few countries:', Array.from(items).slice(0,10).map(el => el.querySelector('.country').textContent).join(', '));
            }
        }, 100);
    } catch(e) {
        console.error('JSDOM error:', e);
    }
} else {
    console.log('Script block not found');
}
