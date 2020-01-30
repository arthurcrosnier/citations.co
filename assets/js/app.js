/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import $ from 'jquery';

// create global $ and jQuery variables
global.$ = global.jQuery = $;

const logoPath = require('../images/logo.png');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');
// any CSS you require will output into a single css file (app.css in this case)
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
// la mettre en global sinon ça ne marche pas
import Cropper from 'cropperjs/dist/cropper';
global.Cropper = Cropper;

require('../css/global.scss');
require('../css/app.css');



// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

$(document).ready(function() {

});
