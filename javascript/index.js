require(['jquery'], function($) {
    const log = window.console.log;
    log('index.js');
    class ClassWatcher {

        constructor(targetNode, classToWatch, classAddedCallback, classRemovedCallback) {
            this.targetNode = targetNode;
            this.classToWatch = classToWatch;
            this.classAddedCallback = classAddedCallback;
            this.classRemovedCallback = classRemovedCallback;
            this.observer = null;
            this.lastClassState = targetNode.classList.contains(this.classToWatch);

            this.init();
        }

        init() {
            this.observer = new MutationObserver(this.mutationCallback);
            this.observe();
        }

        observe() {
            this.observer.observe(this.targetNode, { attributes: true });
        }

        disconnect() {
            this.observer.disconnect();
        }

        mutationCallback = mutationsList => {
            for(let mutation of mutationsList) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    let currentClassState = mutation.target.classList.contains(this.classToWatch);
                    if(this.lastClassState !== currentClassState) {
                        this.lastClassState = currentClassState;
                        if(currentClassState) {
                            this.classAddedCallback();
                        }
                        else {
                            this.classRemovedCallback();
                        }
                    }
                }
            }
        }
    }

    $(document).ready(function() {
        const targetNode = document.querySelector('#page');
        const targetClass = 'show-drawer-right';
        const footer = document.getElementById('page-footer');
        const workOnClassAdd = function() {
            log('open');
            if (footer) {
                footer.style.marginRight = '-1rem';
            }

        };
        const workOnClassRemoval = function() {
            log('close');
            if (footer) {
                footer.style.marginRight = '-3rem';
            }
        };

        if(targetNode.classList.contains(targetClass)) {
            workOnClassAdd();
        } else {
            workOnClassRemoval();
        }
        // watch for a specific class change
        new ClassWatcher(targetNode, targetClass, workOnClassAdd, workOnClassRemoval);

        if ($('.format-mintcampus').length) {
            const pageHeader = $('#page-header');
            let courseRating = $('#mintcampuscourserating');
            if (courseRating.length) {
                pageHeader.css('display', 'flex');
                pageHeader.append(courseRating);
                courseRating.find('.ratingtitle + div').css('display', 'flex');
                courseRating.find('.ratingtitle + div').css('align-items', 'center');
            } else {
                const targetElement = document.querySelector('.course-content-header');

                // Create a new MutationObserver
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                            courseRating = $('#mintcampuscourserating');
                            if (courseRating.parent().attr('id') !== 'page-header') {
                                pageHeader.css('display', 'flex');
                                pageHeader.append(courseRating);
                                courseRating.find('.ratingtitle + div').css('display', 'flex');
                                courseRating.find('.ratingtitle + div').css('align-items', 'center');
                                if (!courseRating.hasClass('col-auto')) {
                                    courseRating.addClass('col-auto');
                                    courseRating.removeClass('col');
                                }
                            }
                        }
                    });
                });

                // Configure the observer to watch for additions of child elements
                var observerConfig = {
                    childList: true,
                    subtree: true
                };

                // Start observing the target element
                observer.observe(targetElement, observerConfig);
            }

            // Span a course progress over the whole page width
            const progressBarContainer = $('#coursecontentcollapse0 .progress-bar-container');
            progressBarContainer.parent().removeClass('col-9');
            progressBarContainer.parent().addClass('col');
            progressBarContainer.parent().css('padding', '0');
            progressBarContainer.css('padding', '0');
            progressBarContainer.find('.progress-bar-line').css('margin', '0');

            // Aligning "start course" button left
            const buttonContainer = $('#coursecontentcollapse0 .startcourse');
            buttonContainer.prepend('<div class="d-none d-sm-block col"></div>');
            const button = buttonContainer.find('.col-5');
            if (button) {
                button.removeClass('col-5');
                button.addClass('col');
            }
        }

        if ($('.format-topics').length) {
            // hide secondary-navigation for students
            $('#collapssesection0').hide();
            const firstTopic = $('#coursecontentcollapse0');
            if (!firstTopic.hasClass('show')) {
                firstTopic.addClass('show');
            }
            $('.activity-basis').each(function(index, item) {
                const activity = $(item);
                const afterLink = activity.find('.afterlink');
                const mediaBody = activity.find('.media-body');
                if (afterLink.length && mediaBody.length) {
                    mediaBody.append(afterLink);
                    afterLink.css('display', 'block');
                }
            });

        }

        if ($('.path-mod').length) {
            // keep this checkbox always unchecked
            $('.path-mod #id_appearancehdr #id_appearancehdrcontainer .fitem:last-of-type input').prop('checked', false);
        }

        // Changing logo according to theme mode
        // const body = $('body');
        // const logo = $('.navbar-brand img');
        // if (body.hasClass('dark')) {
        //     logo.attr('src', '/theme/mint/pix/logo_light.png');
        // }
        // if (body.hasClass('light')) {
        //     logo.attr('src', '/theme/mint/pix/logo.png');
        // }
        // $('#usernavigation .switch-wrapper input').change(function() {
        //     if (body.hasClass('dark')) {
        //         logo.attr('src', '/theme/mint/pix/logo_light.png');
        //     }
        //     if (body.hasClass('light')) {
        //         logo.attr('src', '/theme/mint/pix/logo.png');
        //     }
        // });

        // Validating number of chosen topics in course settings
        if ($('#page-course-edit').length) {
            const maxSelection = 3;
            const multipleSelectors = [$('#fitem_id_customfield_mc_moodle_themen select')];

            multipleSelectors.forEach(multipleSelector => {
                // If selecting one by one holding "Ctrl"
                multipleSelector.on('mousedown', function(event) {
                    const selectedOptions = $(this).find('option:selected');
                    const totalSelected = selectedOptions.length;
                    const clickedOption = $(event.target);

                    if (totalSelected >= maxSelection && !clickedOption.is(':selected')) {
                        event.preventDefault();
                        alert('Sie dürfen nur 3 Optionen auswählen.');
                    }
                });

                // If selecting more than 3 options by holding "Shift"
                multipleSelector.on('change', function() {
                    const selectedOptions = $(this).find('option:selected');

                    if (selectedOptions.length > maxSelection) {
                        selectedOptions.prop('selected', false);
                        alert('Sie dürfen nur 3 Optionen auswählen.');
                    }
                });
            });
        }


        // Adds link to Mint-ID in Userprofile
        const mintIdLink = '<a class="btn btn-primary mint-id-link" href="https://mint-id.org/">Zur MINT-ID</a>';
        if ($('#page-user-profile').length) {
            $('.profile_tree > section:first-child').append(mintIdLink);

            // Displaying useful sections
            $('#page-user-profile .profile_tree section').each(function(index, item) {
                const heading = $(item).find('.lead');
                switch (heading.text().toLowerCase()) {
                    case 'kursdetails':
                    case 'course details':
                    case 'datenschutz und richtlinien':
                    case 'privacy and policies':
                    case 'administration':
                        $(item).attr('style','display: inline-block !important');
                        break;
                }
            });
        }
        if ($('#page-user-edit').length || $('#page-user-editadvanced').length) {
            const container = `<div class="row form-group"><div class="col-md-3"></div><div class="col-md-9">${mintIdLink}</div></div>`;
            $('#id_moodlecontainer').append(container);

            // if ($('#id_category_1').hasClass('collapsed')) {
            //     $('#id_category_1').removeClass('collapsed');
            //     $('#id_category_1container').addClass('show');
            // }
            const helpTextHTML = "<div class=\"mt-2\">Mehrfachauswahl über Steuerungs-Taste möglich</div>";
            $('#id_profile_field_interests').parent().append(helpTextHTML);
            $('#id_profile_field_interests').parent().css("flex-flow", "column");
            $('#id_profile_field_subject').parent().append(helpTextHTML);
            $('#id_profile_field_subject').parent().css("flex-flow", "column");
        }

        if ($('#page-login-index').length && window.location.href.search("[?&]staff=") !== -1) {
            $('#page-login-index #guestlogin,#page-login-index #login').show();
        }

        if ($('body.mobiletheme').length) {
            const mobileMenu = $('#theme_boost-drawers-primary .drawercontent .list-group:first-child');
            mobileMenu.append("<a href=\"https://mintcampus.org/fur-lernanbietende\" " +
                "class=\"list-group-item list-group-item-action\">Für Lernanbietende</a>");
        }


        // Barrierearmut
        $('.primary-navigation .nav-item .nav-link').removeAttr('tabindex');
    });
});