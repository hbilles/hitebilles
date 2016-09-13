/**
 * Class for the Textfield component
 *
 * @param HTMLElement
 */
export default class Textfield {
	constructor(el) {
		this.el = el

		this.cssClasses = {
			LABEL: 'textfield__label',
			INPUT: 'textfield__input',
			IS_DIRTY: 'textfield--is-dirty',
			IS_FOCUSED: 'textfield--is-focused',
			IS_DISABLED: 'textfield--is-disabled',
			IS_INVALID: 'textfield--is-invalid',
			IS_UPGRADED: 'textfield--is-upgraded',
			HAS_PLACEHOLDER: 'textfield--has-placeholder'	
		}

		// Initialize the instance
		this.init()
	}

	// Handle css class updates
	updateClasses() {
		this.checkDisabled()
		this.checkValidity()
		this.checkDirty()
		this.checkFocus()
	}

	// Handle Focus
	onFocus() {
		this.el.classList.add(this.cssClasses.IS_FOCUSED)
	}

	// Handle Blur
	onBlur() {
		this.el.classList.remove(this.cssClasses.IS_FOCUSED)
	}

	// Handle reset event
	onReset() {
		this.updateClasses()
	}

	// Check if input is disabled and update field
	checkDisabled() {
		if (this.input.disabled) {
			this.el.classList.add(this.cssClasses.IS_DISABLED)
		} else {
			this.el.classList.remove(this.cssClasses.IS_DISABLED)
		}
	}

	// Check if input is valid and update field
	checkValidity() {
		if (this.input.validity) {
			if (this.input.validity.valid) {
				this.el.classList.remove(this.cssClasses.IS_INVALID)
			} else {
				this.el.classList.add(this.cssClasses.IS_INVALID)
			}
		}
	}

	// Check if the input is dirty and update field
	checkDirty() {
		if (this.input.value && this.input.value.length > 0) {
			this.el.classList.add(this.cssClasses.IS_DIRTY)
		} else {
			this.el.classList.remove(this.cssClasses.IS_DIRTY)
		}
	}

	// Check if the input has focus and update field
	checkFocus() {
		if (Boolean(this.el.querySelector(':focus'))) {
			this.el.classList.add(this.cssClasses.IS_FOCUSED)
		} else {
			this.el.classList.remove(this.cssClasses.IS_FOCUSED)
		}
	}

	// Disable the element
	disable() {
		this.input.disabled = true
		this.updateClasses()
	}

	// Enable the element
	enable() {
		this.input.disabled = false
		this.updateClasses()
	}

	// Update the input value
	update(value) {
		this.input.value = value || ''
		this.updateClasses()
	}

	// Initialize this element
	init() {
		if (this.el) {
			// get the label and input elements
			this.label = this.el.querySelector('.' + this.cssClasses.LABEL)
			this.input = this.el.querySelector('.' + this.cssClasses.INPUT)

			// check if placeholder is set on the input
			if (this.input.hasAttribute('placeholder')) {
				this.el.classList.add(this.cssClasses.HAS_PLACEHOLDER)
			}

			// bind 'this' for event handlers
			this.updateClassesHandler = this.updateClasses.bind(this)
			this.focusHandler = this.onFocus.bind(this)
			this.blurHandler = this.onBlur.bind(this)
			this.resetHandler = this.onReset.bind(this)

			// set event handlers on input element
			this.input.addEventListener('input', this.updateClassesHandler)
			this.input.addEventListener('focus', this.focusHandler)
			this.input.addEventListener('blur', this.blurHandler)
			this.input.addEventListener('reset', this.resetHandler)

			// check if input is currently invalid
			var invalid = this.el.classList.contains(this.cssClasses.IS_INVALID)

			// update element classes
			this.updateClasses()
			this.el.classList.add(this.cssClasses.IS_UPGRADED)

			if (invalid) {
				this.el.classList.add(this.cssClasses.IS_INVALID)
			}

			// check if element is autofocused
			if (this.input.hasAttribute('autofocus')) {
				this.el.focus()
				this.checkFocus()
			}
		}
	}


}
