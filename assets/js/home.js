function fetchHomepage() {
    fetch('/dist/homepage.json')
        .then(response => response.json())
        .then(function (data) {
            data.forEach(function(month) {
               console.log(month)
               document.getElementById('months-list').innerHTML += '<div onclick="location.href=\'/' + month.name + '\'" style="background-image:url(\'' + month.image + '\')" class="month"><p>' + month.name + '</p></div>';
            })
        });
}

fetchHomepage();