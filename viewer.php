<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>Bierwertung – Live Punkte</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body.dark {
      font-family: sans-serif;
      background: black;
      color: white;
      margin: 0;
      padding: 20px;
    }

    body.light {
      font-family: sans-serif;
      background: #f5f5f5;
      color: #000;
      margin: 0;
      padding: 20px;
    }

    h1, h2 {
      text-align: center;
      font-size: 3em;
      margin-bottom: 30px;
    }

    h2 {
      font-size: 2em;
    }

    #scoreboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      max-width: 1600px;
      margin: auto;
    }

    .group {
      padding: 15px;
      border-radius: 10px;
      text-align: center;
    }

    body.dark .group {
      background: #333;
    }

    body.light .group {
      background: #fff;
      border: 1px solid #ccc;
    }

    .group-name {
      display: inline-block;
      font-size: 2em;
      margin-bottom: 10px;
      white-space: nowrap;
    }

    body.dark .points {
      font-size: 1.5em;
      font-weight: bold;
      color: #ccc;
    }

    body.light .points {
      font-size: 1.5em;
      font-weight: bold;
      color: #444;
    }

    .winner {
      background: #ffc107 !important;
    }

    .winner .points,
    .winner .group-name {
      color: black !important;
    }
  </style>
</head>
<body>
  <h2>Bierwertung – Live Punktestand</h2>
  <div id="scoreboard"></div>

  <script>
    const showZeroPoints = false; //<--- Alle Mannschaften anzeigen?

    // Funktion zum Setzen des Themes basierend auf URL
    function setThemeFromUrl() {
      const params = new URLSearchParams(window.location.search);
      const theme = params.get('theme') === 'light' ? 'light' : 'dark';
      document.body.classList.add(theme);
    }

    setThemeFromUrl();

    function fitTextToWidth(element, maxFontSize = 32, minFontSize = 12) {
      let parentWidth = element.parentElement.offsetWidth - 20;
      element.style.fontSize = maxFontSize + 'px';

      while (element.scrollWidth > parentWidth && parseFloat(element.style.fontSize) > minFontSize) {
        element.style.fontSize = (parseFloat(element.style.fontSize) - 1) + 'px';
      }
    }

    function loadScores() {
      fetch('get_scores.php')
        .then(res => res.json())
        .then(data => {
          const sb = document.getElementById('scoreboard');
          sb.innerHTML = '';

          let filteredData = showZeroPoints ? data : data.filter(g => g.points > 0);

          if(filteredData.length === 0){
            sb.textContent = 'Keine Gruppen mit Punkten vorhanden.';
            return;
          }

          const maxPoints = Math.max(...filteredData.map(g => g.points));

          filteredData.forEach(item => {
            const div = document.createElement('div');
            div.className = 'group' + (item.points === maxPoints && maxPoints > 0 ? ' winner' : '');

            const nameEl = document.createElement('div');
            nameEl.className = 'group-name';
            nameEl.textContent = item.name;

            const pointsEl = document.createElement('div');
            pointsEl.className = 'points';
            pointsEl.textContent = item.points + (item.points === 1 ? ' Punkt' : ' Punkte');
            div.append(nameEl, pointsEl);
            sb.appendChild(div);

            requestAnimationFrame(() => {
              fitTextToWidth(nameEl);
            });
          });
        });
    }

    loadScores();
    setInterval(loadScores, 3000);
  </script>
</body>
</html>
