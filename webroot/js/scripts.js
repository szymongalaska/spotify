/**
 * Stars loader
 * @param {*} element 
 */
function loader(element)
{
    $(element).addClass('relative-container');
    $(element).prepend('<div class="loader-overlay"><span class="loader"></span></div>');
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