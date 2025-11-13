async function getData() {
    try {
        const data = await fetch('./data/data.json')
        if (!data.ok) {
            throw new Error(`HTTP error! status: ${data.status}`)
        }
        const response = await data.json()
        renderCards(response)
    }
    catch(err) {
        console.log("Error retrieving data: ", err)
    }
}
function renderCards(cardsData) {
    const container = document.getElementById('browse-car-container')
    let cardsHTML = ""

    cardsData.forEach( (card) => {
        cardsHTML += `
            <div class="card gradient">
                <div class="card-top">
                    <h3 class="card-title">${card.make} ${card.model}</h3>
                    <p class="card-p">or similar | ${card.style}</p>

                    <div class="icon-container">
                        <div class="card-details">
                            <img class="card-people-icon" src="./image/person-icon.svg" alt="number of people icon">
                            <p>${card.capacityPeople}</p>
                        </div>

                        <div class="card-details">
                            <img class="card-luggage-icon" src="./image/luggage-icon.svg" alt="number of luggage icon"> 
                            <p>${card.capacityLuggage}</p>
                        </div>
                        <div class="card-details">
                            <img class="card-transmission-icon" src="./image/automatic-icon.svg" alt="transmission icon"> 
                            <p>${card.transmission}</p>
                        </div>
                    </div>
                </div>

                <div class="card-img-container" >
                    <img src=${card.image} alt="">
                </div>

                <p class="card-price-per-day">$${card.price}/day</p>
            </div>
        `
    } )

    container.innerHTML = cardsHTML
}

getData()