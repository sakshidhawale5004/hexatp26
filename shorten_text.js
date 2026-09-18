const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const oldArray = `            var deadlines = [
                { flag: '🇦🇪', label: 'UAE',             date: new Date(2026,  8, 30), desc: 'CT/income tax return due (incl. TP disclosure)' },
                { flag: '🇭🇰', label: 'HONG KONG SAR',   date: new Date(2026,  8, 30), desc: 'Local File deadline' },
                { flag: '🇵🇱', label: 'POLAND',          date: new Date(2026,  9, 31), desc: 'Local File deadline' },
                { flag: '🇮🇳', label: 'INDIA',           date: new Date(2026,  9, 31), desc: 'TP disclosure / annual filing due' },
                { flag: '🇧🇪', label: 'BELGIUM',         date: new Date(2026, 11, 31), desc: 'TP disclosure / annual filing due' },
                { flag: '🇭🇺', label: 'HUNGARY',         date: new Date(2026, 11, 31), desc: 'Master File deadline' },
                { flag: '🇰🇷', label: 'SOUTH KOREA',     date: new Date(2026, 11, 31), desc: 'Local File deadline' },
                { flag: '🇿🇦', label: 'SOUTH AFRICA',    date: new Date(2026, 11, 31), desc: 'Local File deadline' }
            ];`;

const newArray = `            var deadlines = [
                { flag: '🇦🇪', label: 'UAE',             date: new Date(2026,  8, 30), desc: 'CT return due' },
                { flag: '🇭🇰', label: 'HONG KONG SAR',   date: new Date(2026,  8, 30), desc: 'Local File' },
                { flag: '🇵🇱', label: 'POLAND',          date: new Date(2026,  9, 31), desc: 'Local File' },
                { flag: '🇮🇳', label: 'INDIA',           date: new Date(2026,  9, 31), desc: 'TP filing' },
                { flag: '🇧🇪', label: 'BELGIUM',         date: new Date(2026, 11, 31), desc: 'TP filing' },
                { flag: '🇭🇺', label: 'HUNGARY',         date: new Date(2026, 11, 31), desc: 'Master File' },
                { flag: '🇰🇷', label: 'SOUTH KOREA',     date: new Date(2026, 11, 31), desc: 'Local File' },
                { flag: '🇿🇦', label: 'SOUTH AFRICA',    date: new Date(2026, 11, 31), desc: 'Local File' }
            ];`;

if (html.includes(oldArray)) {
    html = html.replace(oldArray, newArray);
    fs.writeFileSync('index.html', html, 'utf8');
    console.log('Descriptions shortened');
} else {
    console.log('oldArray not found');
}
