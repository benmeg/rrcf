<?php

define('TITLE', "Frequently Asked Questions (FAQ)");
include "../assets/layouts/header.php";
?>

<script>

// https://john-dugan.com/javascript-debounce/
// https://codepen.io/johndugan/pen/BNwBWL?editors=001
var debounce = function(func, wait, immediate) {

  'use strict';

  var timeout;
  return function() {
    var context = this;
    var args = arguments;
    var later = function() {
      timeout = null;
      if ( !immediate ) {
        func.apply(context, args);
      }
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait || 200);
    if ( callNow ) {
      func.apply(context, args);
    }
  };
};



// peek-a-boo.7.3.js - Mike Foskett - https://websemantics.uk/articles/peek-a-boo-v7/

// Show - hide a block - adapted for FAQ
// Requires:
//    setAttribute / getAttribute (IE9+)
//    classList (IE10+)  - disabled
//    addEventListener (IE9+)
//    requestAnimationFrame (IE10+) - replace with requestAF() for IE9
//    querySelectorAll
//    preventDefault
//    debounce()

	

// FAQ version:
// v7.4 Added: open an question from an internal anchor
// v7.3 Expanded when URI fragment matches the target ID
// v7.2 HTML button reinstated, js adjusted.
//			Initial open/close state reworked


var Pab = (function (window, document, debounce) {
	
	// Terminology used:
	// toggle - The dynamically added button used to toggle the hidden content
	// target - The object which contains the hidden content
	// toggleParent - The object which will, or does, contain the toggle button

  'use strict';

  var dataAttr = 'data-pab';
  var attrName = dataAttr.replace('data-', '') + '_';
  var btnClass = dataAttr.replace('data-', '') + '-btn';
	var dataExpandAttr = dataAttr + '-expand';
  var internalId = 1;


  function $ (selector) {
    return Array.prototype.slice.call(document.querySelectorAll(selector));
  }


  function _isExpanded (obj) { // or not aria-hidden
    return obj && (obj.getAttribute('aria-expanded') === 'true' || obj.getAttribute('aria-hidden') === 'false');
  }


	// This function is globally reusable. Perhaps externalise for reuse?  
	// Get height of an element object
  // Assumes it is hidden by max-height: 0 in the CSS
	var _getHiddenObjectHeight = function (obj) {
    obj.setAttribute('style', 'max-height: none');
		var height = obj.scrollHeight;
    obj.removeAttribute('style');
		return height;
	};

/* Not enough support to be truly useful.
   Under most circumstance aria-expanded is sufficient.
  var _setToggleSvgTitle = function(toggle) {
    var title = toggle.getElementsByTagName('title');
    if (title && title[0]) {
      title[0].innerHTML = _isExpanded(toggle) ? 'Hide' : 'Show';
    }
  };
*/

  var _openCloseToggleTarget = function (toggle, target, isExpanded) {
    toggle.setAttribute('aria-expanded', !isExpanded);
    _setToggleMaxHeight(target);
    window.requestAnimationFrame(function(){
      target.setAttribute('aria-hidden', isExpanded);
    });
    // _setToggleSvgTitle(toggle); - not enough support to be useful
  };


/* // deprecated to support IE 9 FAQ
  var _toggleParentClass = function (event) {
    var cls = attrName + 'btn_';
    switch (event.type) {
      case 'focus' :
        //event.target.parentNode.classList.add(cls + 'focused');
        event.target.parentNode.className += ' ' + cls + 'focused';
        break;
      case 'blur' :
        event.target.parentNode.classList.remove(cls + 'focused');
        break;
      case 'mouseover' :
        event.target.parentNode.classList.add(cls + 'hovered');
        break;
      case 'mouseout' :
        event.target.parentNode.classList.remove(cls + 'hovered');
        break;
    }
  };
*/


  var _setToggleMaxHeight = function (target) {
    if (_isExpanded(target)) {
      // max-height overidden by CSS !important
      // target.style.maxHeight = 0;
    } else {
      target.style.maxHeight = _getHiddenObjectHeight(target) + 'px';
    }
  };

  var _toggleClicked = function (event) {

    var toggle = event.target;
    var target;
    var isExpanded;

    if (toggle) {

			// To prevent children bubbling up to parent causing more than one click event
			event.stopPropagation();

      target = document.getElementById(toggle.getAttribute('aria-controls'));
      if (target) {
        isExpanded = _isExpanded(toggle);
        _openCloseToggleTarget(toggle, target, isExpanded);
      }
    }
  };


  var _addToggleListeners = function (toggle) {
    // Simpler to mangage here rather than in a global handler (consider hover and blur)

    // Parent of toggle and target - Deprecated to support IE 9
    //toggle.addEventListener('focus', _toggleParentClass, false);
    //toggle.addEventListener('blur', _toggleParentClass, false);
    //toggle.addEventListener('mouseout', _toggleParentClass, false);
    //toggle.addEventListener('mouseover', _toggleParentClass, false);

    toggle.addEventListener('click', _toggleClicked, false);

  };


  var _setUpToggle = function (toggle) {

		// Create a html button, add content from parent, replace original parent content.
		var btn = document.createElement('button');
		
		btn.className = btnClass;
		btn.innerHTML = toggle.innerHTML;
		btn.setAttribute('aria-expanded', false);
		btn.setAttribute('id', attrName + internalId++);
		btn.setAttribute('aria-controls', toggle.getAttribute(dataAttr));

		toggle.innerHTML = '';
		toggle.appendChild(btn);
		
		return btn;
	};


	// Prestating the container class in the HTML allows the CSS to render before JS kicks in.
	// Add container class to parent if not prestated
  var _setUpToggleParent = function (toggle) {
    var parent = toggle.parentElement;
    if (parent && !parent.className.match(attrName + 'container')) {
      //parent.classList.add(attrName + 'container');
      parent.className += ' ' + attrName + 'container';
    }
  };


  var _addToggleSVG = function (toggle) {
    var clone = toggle.cloneNode(true);
    if (!clone.innerHTML.match('svg')) {

			// HTML SVG definition allows more control
      clone.innerHTML += '<svg role=presentational focusable=false class=' + dataAttr.replace('data-', '') + '-svg-plus><use class=\"use-plus\" xlink:href=\"#icon-vert\" /><use xlink:href=\"#icon-hori\"/></svg>';
      //requestAnimationFrame(function () {
        toggle.parentElement.replaceChild(clone, toggle);
      //});
    }
    return clone;
  };


  var _setUpTargetAria = function (toggle, target) {
    target.setAttribute('aria-hidden', !_isExpanded(toggle));
    target.setAttribute('aria-labelledby', toggle.id);
  };


  var _resetAllTargetsMaxHeight = function () {
    var toggles = document.querySelectorAll('[' + dataAttr + ']');
    var i = toggles.length;
    var target;
    while (i--) {
      target = document.getElementById(toggles[i].getAttribute(dataAttr));
      if (target) {
        target.style.maxHeight = _getHiddenObjectHeight(target) + 'px';
      }
    }
  };


	var isMustardCut = function () {
		return (document.querySelectorAll && document.addEventListener);
	};


	var _openIfRequired = function (toggle, target) {
		
		var fragmentId = window.location.hash.replace('#', '');
		
		// Expand by default 'data-pab-expand' small delay applied
		if (toggle.parentElement.hasAttribute(dataExpandAttr)) {
			setTimeout(function () {
				_openCloseToggleTarget(toggle, target, _isExpanded(toggle));
			}, 500);
		}
    

		// Check url fragment and if target ID matches, open it
		if (target.id === fragmentId) {
			setTimeout(function () {
				_openCloseToggleTarget(toggle, target, false);
				toggle.focus();
			}, 500);
		}

	};


	var addSingleToggleTarget = function (toggleParent) {

		var targetId = toggleParent.getAttribute(dataAttr);
		var target = document.getElementById(targetId);
		var toggle;

		if (target && isMustardCut) {
			toggle = _setUpToggle(toggleParent);
			_setUpToggleParent(toggleParent);
			toggle = _addToggleSVG(toggle);
			_setUpTargetAria(toggle, target);
			_addToggleListeners(toggle);
			_openIfRequired(toggle, target);
		}
	};
  
  var hashChanged = function (e) {
    var fragmentId = window.location.hash.replace('#', '');
    var toggle = document.querySelector('#' + fragmentId + ' > .' + btnClass);
    var target = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!toggle || !target) {return false;}

    toggle.focus();
    toggle.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'});

    _openCloseToggleTarget(toggle, target, false);
  };


  var addToggles = function () {

		// Iterate over all toggles (elements with the 'data-pab' attribute)
		var togglesMap = $('[' + dataAttr + ']').reduce(function (temp, toggleParent) {
			addSingleToggleTarget(toggleParent);
			return true;
		}, {});

    return true;
  };


	if (isMustardCut) {
		window.addEventListener('load', addToggles, false);

		// Recalculate all target max-heights after (debounced) window is resized.
		window.addEventListener('resize', debounce(_resetAllTargetsMaxHeight, 500), false);
    
    // On fragment change
		window.addEventListener('hashchange', hashChanged, false);
	}


  return {
    // Exposes an addition function to the global scope allowing toggle & target to be added dynamically.
		add: addSingleToggleTarget
  };


}(window, document, debounce));

// To add dynamically created toggles:
// Pab.add(toggle-object); // Add individual toggle & target


// setTimeout(function(){
//   document.querySelector('.pab_container').innerHTML += `
//   <dt data-pab=faq_6><span>Test dynamic insertion</span></dt>
//   <dd id=faq_6>
//     <div>
//       <p>Dynamically added to <code>dl</code>.</p>
//     </div>
//   </dd>`;
//   Pab.add(document.querySelector('[data-pab=faq_6]'));
// }, 2000);

// setTimeout(function(){
//   document.getElementById('injection_point').innerHTML += `
//   <div data-pab=faq_7><span>Test dynamic insertion</span></div>
//   <div id=faq_7>
//     <div>
//       <p>Dynamically added externally to the <code>dl</code>.</p>
//     </div>
//   </div>`;
//   Pab.add(document.querySelector('[data-pab=faq_7]'));
// }, 2000);

</script>


<style>

body{
  padding: 0;
  font-weight:100;
  scroll-behavior: smooth;
}


/* FAQ container */

.dl-faq {
  position: relative;
  max-width: 70rem;
  margin: 2rem auto 3rem;
}

.dl-faq > dt {
  font-size: 1.2rem;
  font-weight: 100;
  padding: 1rem;

  /* Fix for IE9 & 10 */
  border-top: 1px solid rgba(255,255,255,.2);
}

dt > button {
  color: inherit;
  background-color: inherit;
}
.dl-faq > dt:first-child .pab-btn,
.dl-faq > dt:first-child {
  border-top: 0;
}

.dl-faq.pab_container > dt {
  /* added via JS */
  padding: 0;
}

.dl-faq > dd {
  margin: 0 auto;
  padding: 0 1.5em;
  font-weight:100;
}

.dl-faq > dd > div {
  padding: 0 0 2rem;
}

.dl-faq div > p {
  margin: 0 0 1rem;
}

.dl-faq div >:last-child {
  margin: 0;
}


/* The acivating buttons added via JS */

.pab-btn {
  position: relative;
  cursor: pointer;
  transition: color .3s ease-in;

  /* Using absolute positioning for SVG so reserve some space */
  padding: 1rem 2.5rem 1rem .5rem;
  border: 0 solid transparent;
  border-top: 1px solid rgba(0,0,0,.75);

  /* inherit doesn't work in IE */
  font-size: inherit;
  text-align: left;
  width: 100%;
}

.pab-btn:hover,
.pab-btn:focus,
.pab-btn:active {
  color:#fff;
  background-color: rgba(0,0,0,.25);
}

.pab-btn:focus {
  outline: 0 solid;
}

.pab-btn::-moz-focus-inner {
  border: 0;
  padding: 0;
  margin-top: -2px;
  margin-bottom: -2px;
}


/* Underline text on button hover (Tesco requirement) */

.pab-btn > span {
  position: relative;
  /* Removes button drepression in IE */
  pointer-events: none;
  /* Required by Safari */
  border-bottom: 1px solid transparent;
  transition: border-color .3s;
}

.pab-btn:hover > span,
.pab-btn:focus > span {
  border-bottom-color: rgba(255,255,255,.5);
}

.pab-btn:active > span {
  border-bottom-color: transparent;
}


/* SVG plus */

.pab-svg-plus {
  /* Tesco requirement
  border: 2px solid currentColor; */
  border-radius: 100%;
  display: block;
  position: absolute;
  top: calc(50% - .75em);
  right: 4px;
  width: 1.5em;
  height: 1.5em;
  margin: 0;
  pointer-events: none;
  stroke-width: 4;
  stroke-linecap: square;
  stroke: currentColor;
  -webkit-transition: transform .7s ease-out, box-shadow .3s ease-out;
  transition: transform .7s ease-out, box-shadow .3s ease-out;
}

.pab-btn:hover .pab-svg-plus,
.pab-btn:focus .pab-svg-plus {
  /* Same colour as text but with .4 alpha */
  /* Tesco requirement
  box-shadow: 0 0 0 4px rgba(0, 83, 159, 0.4);*/
}

.pab-btn:active .pab-svg-plus {
  /* Tesco requirement
  box-shadow: 0 0 0 4px rgba(0, 83, 159, 0);*/
}

[aria-expanded="true"] > .pab-svg-plus {
  transform: rotateZ(360deg);
}

.use-plus {
  /* used to animate plus into minus */
  -webkit-transition: stroke .5s ease-out, opacity .7s ease-out;
  transition: stroke .5s ease-out, opacity .7s ease-out;
}

[aria-expanded=true] .use-plus {
  opacity: 0;
}

.isSafari .pab-btn .pab-svg-plus {
  box-shadow: none;
}


/* Open / close animation - The inaccurate CSS max-height is resolved via JS adding an inline style */

[data-pab] + [aria-hidden] {
  overflow: hidden;
  opacity: 1;
  max-height: 50rem;
  visibility: visible;
  transition: visibility 0s ease 0s, max-height .65s ease-out 0s, opacity .65s ease-in 0s;
}

[data-pab] + [aria-hidden="true"] {
  max-height: 0;
  opacity: 0;
  visibility: hidden;
  transition-delay: .66s, 0s, 0s;
}


/* Overide the max-height set as an inline style by the JS */

[data-pab] + [style][aria-hidden="true"] {
  max-height: 0 !important;
}
</style>


<main>
<h2>Frequently Asked Questions (FAQ)</h2>

<dl class="dl-faq pab_container">

  <dt id=q_1 data-pab=faq_1><span>Why do I need to register?</span></dt>

  <dd id=faq_1>
    <div>
      <p>Like many online services that enable anonymous public comments (e.g. PubPeer), Registered Reports Community Feedback requires users to register with an account.</p>

      <p>Creating a user account allows users who are returning to give Stage 2 feedback on a specific manuscript, to link that Stage 2 feedback to existing Stage 1 feedback for the same manuscript.</p>

      <p>For prospective feedback (e.g. feedback where Stage 2 peer review has yet to occur, and where the user has given Stage 1 feedback) a user account enables users to leave feedback per stage and link those stages.</p>

      <p>A user account also allows for authors/reviewers of multiple manuscripts to manage/distinguish between the various pieces of feedback they have given.For instance, a user can view the Stage 1 feedback they have previously given, before giving linked Stage 2 feedback. A logged in user may also choose to invite co-authors to give feedback on the same manuscript (in the case of author feedback).</p>

      <p>For these reasons you will need to register for an account to give feedback. We only require that you register a functioning email address (your name is not required) and the email address does not need to be one that identifies you by name.</p>
    </div>
  </dd>


  <!-- adding data-pab-expand will force section open by default -->
  <dt id=q_2 data-pab=faq_2><span>Who can view the feedback I give?</span></dt>

  <dd id=faq_2>
    <div>
      <p>Only the team members who are part of the research project can view individual contributions made by users, and those team members are bound by confidentiality requirements of our ethical approval not to use or divulge anything outside of the team.</p>

      <p>On the public facing side, no individual ratings are shown — only average anonymised ratings — and to further protect user anonymity, no aggregate data are publicly shown for a journal/platform until at least five distinct sets of feedback have been left for that outlet. This means that an author who intends to leave negative feedback for a particular journal/platform, but is worried that doing so might reveal their identity due to a low number of submissions, needn't worry about being inadvertently identified.</p>
    </div>
  </dd>


  <dt id=q_3 data-pab=faq_3><span>Can you explain the different numbers shown on the dashboard?</span></dt>

  <dd id=faq_3>
    <div>
      <p>The number of ratings (after a journal's name) is the total quantity of feedback completed for that journal - a user completing feedback for both Stages 1 and 2 of a manuscript equates to two ratings.</p>

      <p>The numbers in parentheses (after speed/quality category averages) is the number of valid responses (that contribute to the average rating) given across all feedback completed for that journal, in each category.</p>

      <p>The number of ratings is different from the number of responses because there are multiple questions (and responses) within each category for the feedback that a user gives. For example, a user giving feedback as an author at Stage 1 will be presented with three questions within the Speed category, and may give up to three responses.</p>
    </div>
  </dd>

  <dt id=q_4 data-pab=faq_4><span>What will happen to the website data?</span></dt>

  <dd id=faq_4>
    <div>
      <p>Beyond being shared in aggregate on our <a href="../dashboards">live dashboards</a>, the data will be used for empirical research, primarily as part of a PhD project (see 'Who is behind this website?' below). Alongside this, the intention is to publish the findings in the academic literature.</p>

      <p>After publication, we plan to share aggregate, anonymised rating data in a public repository under an open licence (CC BY). Any optional comments provided will not be shared publicly, but may be used for qualitative analysis, to identify themes relating to Registered Reports peer review, including strengths and weaknesses of the format.</p>

      <p>Any and all personal data collected will never be shared outside our research team, and will be deleted within 15 years of the end of the project.</p>

      <p>For more information, see our <a href="../data-policy.php" title="Click here to read our Data Policy" rel="modal:open">Data Policy</a>.</p>
    </div>
  </dd>

  <dt id=q_5 data-pab=faq_5><span>Does this research project have ethical approval?</span></dt>

  <dd id=faq_5>
    <div>
      <p>Yes, this research project received a Favourable Opinion on 20th July 2022 by Cardiff University's School of Psychology Research Ethics Committee. For more information you can view the <a href="../consent-form.php" title="Click here to read our Consent Form" rel="modal:open">Consent Form</a> and <a href="../data-policy.php" title="Click here to read our Data Policy" rel="modal:open">Data Policy</a>. Any personal data provided is processed in accordance with the General Data Protection Regulation (GDPR).</p>
    </div>
  </dd>

  <dt id=q_6 data-pab=faq_6><span>I'm having problems verifying my account</span></dt>

  <dd id=faq_6>
    <div>
      <p>If you receive an error when using the email verification link, it is most likely that your account has already been verified (the link will only work once).<br /><br />Please try <a href="../login/">logging in</a> directly.</p>
    </div>
  </dd>

  <dt id=q_7 data-pab=faq_7><span>Who is behind this website?</span></dt>

  <dd id=faq_7>
    <div>
      <p>The team behind the website are <a target="_blank" href="https://www.cardiff.ac.uk/people/research-students/view/2463384-">Ben Meghreblian</a> (PhD student), <a target="_blank" href="https://www.cardiff.ac.uk/people/view/133632-chambers-chris">Chris Chambers</a> (Supervisor), and <a target="_blank" href="https://www.cardiff.ac.uk/people/view/1718168-tzavella-loukia">Loukia Tzavella</a> (Supervisor) - all based at <a target="_blank" href="https://www.cardiff.ac.uk/campus-developments/projects/cubric">Cardiff University Brain Research Imaging Centre (CUBRIC)</a>. The website forms part of Ben's PhD, which has a working title of "Encouraging Registered Reports – Metascience and Tool Development".</p>
    </div>
  </dd>

</dl>

<div id=injection_point></div>

<!-- <p>Can an anchor <a href="#q_4">open an answer</a> from just an id reference?</p>

<p>GitHub repo: <a target=_blank title="[new window]" href="https://github.com/2kool2/accessible-faq">accessible-faq</a></p>

<br> -->

<svg style="display:none">
		<defs>

			<symbol viewBox="0 0 38 38" id="icon-plus">
				<path class="icon-plus-v" d="M10.5 19l17 0"></path>
				<path class="icon-plus-h" d="M19 10.5l0 17"></path>
			</symbol>

			<symbol viewBox="0 0 38 38" id="icon-minus">
				<path class="icon-plus-v" d="M10.5 19l17 0"></path>
			</symbol>
			
			<!-- vert and hori combined make up the plus icon and allow for animation -->
			<symbol viewBox="0 0 38 38" id="icon-vert">
				<path d="M19 10.5l0 17"></path>
			</symbol>
			<symbol viewBox="0 0 38 38" id="icon-hori">
				<path d="M10.5 19l17 0"></path>
			</symbol>

		</defs>
	</svg>
</main>

<?php
include '../assets/layouts/footer.php';
?>