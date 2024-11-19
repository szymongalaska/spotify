/**
 * Stars loader
 * @param {*} element 
 */
function loader(element)
{
    $(element).addClass('relative-container');
    $(element).prepend('<div class="loader-overlay"><i class="fa-solid fa-spinner fa-spin fa-2xl" style="color: var(--color-green-light)"></i></div>');
}

/**
 * Stops loader
 * @param {*} element 
 */
function loaderStop(element)
{
    $(element).removeClass('relative-container');
    $('div.loader-overlay').remove();
}