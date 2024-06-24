/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it } from '@jest/globals';

/**
 * Internal dependencies
 */
import { CarouselNav } from '../carousel-nav';

let itemsContainer;

describe('CarouselNav', () => {
	beforeEach(() => {
		itemsContainer = document.createElement('ul');
		for (let i = 0; i < 10; i += 1) {
			const item = document.createElement('li');
			item.innerText = `item ${i}`;
			item.setAttribute('data-label', `item ${i}`);
			item.setAttribute('id', `item-${i}`);
			itemsContainer.appendChild(item);
		}

		document.body.appendChild(itemsContainer);
	});

	afterEach(() => {
		document.body.removeChild(itemsContainer);
		itemsContainer = null;
	});

	it('has main elements', () => {
		let currentPage = itemsContainer.querySelector('li:nth-of-type(3)');
		const namespace = 'my-carousel';
		const items = itemsContainer.querySelectorAll('li');

		const { container } = render(
			<CarouselNav
				currentPage={currentPage}
				items={items}
				namespace={namespace}
				nextButtonDisabled={false}
				prevButtonDisabled={false}
				setCurrentPage={(newItem) => {
					currentPage = newItem;
				}}
				centeredItemIndex={5}
				showDots={true}
			/>
		);

		expect(
			container.querySelector('#my-carousel__prev-button:not(:disabled)')
		).not.toBeNull();
		expect(
			container.querySelector('#my-carousel__next-button:not(:disabled)')
		).not.toBeNull();
	});
});
