/** 
  Requirement: Populate the "Weekly Course Breakdown" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="week-list-section"` to the
     <section> element that will contain the weekly articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the week list ('#week-list-section').


// --- Functions ---

/**
 * TODO: Implement the createWeekArticle function.
 * It takes one week object {id, title, startDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * - The "View Details & Discussion" link's `href` MUST be set to `details.html?id=${id}`.
 * (This is how the detail page will know which week to load).
 */
function createWeekArticle(week) {
  // ... your implementation here ...
  

  const{id,title,startDate,description}=week;
  const article=document.createElement('article');
  const h2=document.createElement('h2');
  h2.textContent=title || '';
  article.appendChild(h2);

  if(startDate){
    
    const dateP=document.createElement('P');
    dateP.textContent = `Starts on: ${startDate}`;
    article.appendChild(dateP);
  }

    const descP=document.createElement('P');
    descP.textContent=description || '';
    article.appendChild(descP);

    const detailsLink = document.createElement('a');
    detailsLink.href = `details.html?id=${id}`;
    detailsLink.textContent = 'View Details & Discussion';
    article.appendChild(detailsLink);

    return article;
  }



/**
 * TODO: Implement the loadWeeks function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the weeks array. For each week:
 * - Call `createWeekArticle()`.
 * - Append the returned <article> element to `listSection`.
 */


async function loadWeeks() {
  // ... your implementation here ...
  const listSection = document.querySelector('#week-list-section');
  if (!listSection) return;


 const response = await fetch('weeks.json');
       
 const weeks = await response.json();


 listSection.innerHTML='';

 weeks.forEach(week => {
  const article=createWeekArticle(week);
  listSection.appendChild(article);
 });

 
}
document.addEventListener('DOMContentLoaded', loadWeeks);
