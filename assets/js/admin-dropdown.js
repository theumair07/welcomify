/**
 * Welcomify Custom Dropdown Component
 * Modern, accessible dropdown with keyboard navigation and grouping
 * 
 * @since 1.4.0
 */
(function () {
    'use strict';

    // Store all dropdown instances
    const dropdowns = [];

    /**
     * Initialize all custom dropdowns on the page
     */
    function initDropdowns() {
        const dropdownElements = document.querySelectorAll('.umy-wdw-dropdown');

        dropdownElements.forEach(function (element) {
            new UmyDropdown(element);
        });
    }

    /**
     * Custom Dropdown Class
     */
    function UmyDropdown(container) {
        this.container = container;
        this.trigger = container.querySelector('.umy-wdw-dropdown-trigger');
        this.panel = container.querySelector('.umy-wdw-dropdown-panel');
        this.optionsList = container.querySelector('.umy-wdw-dropdown-options');
        this.hiddenInput = container.querySelector('input[type="hidden"][name*="template"]');
        this.siteInput = container.querySelector('input[type="hidden"][name*="site"]');
        this.selectedText = container.querySelector('.umy-wdw-dropdown-selected');
        this.isOpen = false;
        this.focusedIndex = -1;
        this.options = [];

        this.init();
        dropdowns.push(this);
    }

    UmyDropdown.prototype = {
        init: function () {
            // Ensure trigger is focusable for keyboard navigation
            if (this.trigger && !this.trigger.hasAttribute('tabindex')) {
                this.trigger.setAttribute('tabindex', '0');
            }
            this.cacheOptions();
            this.bindEvents();
            this.setInitialSelection();
        },

        cacheOptions: function () {
            const optionElements = this.optionsList.querySelectorAll('.umy-wdw-dropdown-option');
            this.options = Array.from(optionElements);

            // Every option needs an id, so the trigger can point screen readers at the active one.
            const baseId = this.panel.id || 'umy-wdw-tpl-list-' + dropdowns.length;
            this.options.forEach(function (option, index) {
                if (!option.id) {
                    option.id = baseId + '-opt-' + index;
                }
            });

            // Translated placeholder text, taken from the empty option printed by PHP.
            const emptyTitle = this.optionsList.querySelector('.umy-wdw-dropdown-option[data-value=""] .umy-wdw-dropdown-option-title');
            this.placeholderText = emptyTitle ? emptyTitle.textContent.trim() : '';
        },

        bindEvents: function () {
            const self = this;

            // Toggle dropdown on trigger click
            this.trigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggle();
            });

            // Option selection
            this.options.forEach(function (option) {
                option.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.selectOption(this);
                });
            });

            // Keyboard navigation on container
            this.container.addEventListener('keydown', function (e) {
                self.handleKeydown(e);
            });

            // Close on outside click
            document.addEventListener('click', function (e) {
                if (!self.container.contains(e.target)) {
                    self.close();
                }
            });
        },

        setInitialSelection: function () {
            const selectedValue = this.hiddenInput ? this.hiddenInput.value : '';

            if (selectedValue) {
                const selectedOption = this.optionsList.querySelector(
                    '.umy-wdw-dropdown-option[data-value="' + selectedValue + '"]'
                );

                if (selectedOption) {
                    this.selectOption(selectedOption, false);
                }
            }
        },

        toggle: function () {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        open: function () {
            // Close other dropdowns first
            dropdowns.forEach(function (dropdown) {
                if (dropdown !== this) {
                    dropdown.close();
                }
            }, this);

            this.isOpen = true;
            this.container.classList.add('is-open');
            this.trigger.setAttribute('aria-expanded', 'true');
        },

        close: function () {
            this.isOpen = false;
            this.container.classList.remove('is-open');
            this.trigger.setAttribute('aria-expanded', 'false');
            this.focusedIndex = -1;
            this.clearFocus();
        },



        selectOption: function (optionElement, shouldClose) {
            if (shouldClose === undefined) shouldClose = true;

            const value = optionElement.dataset.value;
            const site = optionElement.dataset.site || '';
            const text = optionElement.querySelector('.umy-wdw-dropdown-option-title')?.textContent
                || optionElement.textContent.trim();
            const label = optionElement.querySelector('.umy-wdw-dropdown-option-label')?.textContent || '';

            // Update hidden inputs
            if (this.hiddenInput) {
                this.hiddenInput.value = value;
            }
            if (this.siteInput) {
                this.siteInput.value = site;
            }

            // Update selected text display
            if (this.selectedText) {
                if (value === '') {
                    this.selectedText.innerHTML = '<span class="umy-wdw-dropdown-placeholder">' + this.escapeHtml(this.placeholderText) + '</span>';
                } else {
                    // Convert label to class name (e.g., "Bricks Template" -> "label-bricks-template")
                    const labelClass = label ? 'label-' + label.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '') : '';

                    let displayHtml = '<span class="umy-wdw-dropdown-selected-title">' + this.escapeHtml(text) + '</span>';
                    if (label) {
                        displayHtml += '<span class="umy-wdw-dropdown-selected-label ' + labelClass + '">' + this.escapeHtml(label) + '</span>';
                    }
                    this.selectedText.innerHTML = displayHtml;
                }
            }

            // Update selected state on options
            this.options.forEach(function (opt) {
                opt.classList.remove('is-selected');
                opt.setAttribute('aria-selected', 'false');
            });
            optionElement.classList.add('is-selected');
            optionElement.setAttribute('aria-selected', 'true');

            // Drafts are only visible to users who can edit them: the role card shows a note.
            const card = this.container.closest('.umy-wdw-role-card');
            if (card) {
                card.classList.toggle('is-draft', optionElement.dataset.status === 'draft');
            }

            if (shouldClose) {
                this.close();
                this.trigger.focus();
            }
        },

        handleKeydown: function (e) {
            if (!this.isOpen && (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown')) {
                e.preventDefault();
                this.open();
                return;
            }

            if (!this.isOpen) return;

            const visibleOptions = this.options.filter(function (opt) {
                return opt.style.display !== 'none';
            });

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.focusedIndex = Math.min(this.focusedIndex + 1, visibleOptions.length - 1);
                    this.updateFocus(visibleOptions);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    this.focusedIndex = Math.max(this.focusedIndex - 1, 0);
                    this.updateFocus(visibleOptions);
                    break;

                case 'Enter':
                    e.preventDefault();
                    if (this.focusedIndex >= 0 && visibleOptions[this.focusedIndex]) {
                        this.selectOption(visibleOptions[this.focusedIndex]);
                    }
                    break;

                case 'Escape':
                    e.preventDefault();
                    this.close();
                    this.trigger.focus();
                    break;

                case 'Tab':
                    this.close();
                    break;
            }
        },

        updateFocus: function (visibleOptions) {
            this.clearFocus();

            if (this.focusedIndex >= 0 && visibleOptions[this.focusedIndex]) {
                visibleOptions[this.focusedIndex].classList.add('is-focused');
                visibleOptions[this.focusedIndex].scrollIntoView({ block: 'nearest' });
                // Focus stays on the trigger; this tells screen readers which option is active.
                this.trigger.setAttribute('aria-activedescendant', visibleOptions[this.focusedIndex].id);
            }
        },

        clearFocus: function () {
            this.options.forEach(function (opt) {
                opt.classList.remove('is-focused');
            });
            this.trigger.removeAttribute('aria-activedescendant');
        },

        escapeHtml: function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
})();
