import React from 'react';
import {__, _n, _x, sprintf} from '@wordpress/i18n';
import './table-nav.css';

function PagingInput(props) {
    const {maxPage, page, onChangeInput} = props;

    return (
        <span className="paging-input">
            {' '}
            <span className="total-pages">{maxPage}</span>
            {' '}
            {_x('of', 'pagination, e.g. \'8 of 1.\'', 'snpb')}
            {' '}
            <label htmlFor="current-page-selector" className="screen-reader-text">
                {__('Current page', 'snpb')}
            </label>
            <input
                id="current-page-selector"
                className="current-page"
                type="number"
                value={page}
                size="1"
                min="1"
                max={maxPage}
                aria-describedby="table-paging"
                onChange={onChangeInput}
            />
            <span className="tablenav-paging-text"/>
        </span>
    );
}

function PagingText(props) {
    const {page, maxPage} = props;

    return (
        <span id="table-paging" className="paging-input">
            <span className="tablenav-paging-text">
                {' '}
                <span className="total-pages">{maxPage}</span>
                {' '}
                {_x('of', 'pagination, e.g. \'8 of 1.\'', 'snpb')}
                {' '}
                {page}
            </span>
        </span>
    )
}

function PageButton(props) {
    const {
        active,
        className,
        screenReaderText,
        children,
        onClickButton
    } = props;

    if (active) {
        return (
            <a
                className={className + '  button'}
                href="#"
                onClick={onClickButton}
            >
                <span className="screen-reader-text">{screenReaderText}</span>
                <span aria-hidden="true"/>
                {children}
            </a>
        );
    } else {
        return (
            <span
                className="tablenav-pages-navspan button disabled"
                aria-hidden="true"
            >
                {children}
            </span>
        );
    }
}


export default function TableNav(props) {
    const {
        which,
        page,
        maxPage,
        foundRows,
        onClickFirstPage,
        onClickPrevPage,
        onClickNextPage,
        onClickLastPage,
        onChangeInput = () => {}
    } = props;

    const tableNavClass = "tablenav " + which;

    return (
        <div className={tableNavClass}>
            <div className="tablenav-pages">
                <span className="displaying-num">
                    {sprintf(
                        /* translators: number of items. */
                        _n('%d item', '%d items', foundRows, 'snpb'), foundRows)
                    }
                </span>
                <span className="pagination-links">
                    <PageButton
                        active={page > 1}
                        className="first-page"
                        screenReaderText={__('First page', 'snpb')}
                        onClickButton={onClickFirstPage}
                    >{'«'}</PageButton>
                    {' '}
                    <PageButton
                        active={page > 1}
                        className="prev-page"
                        screenReaderText={__('Previous page', 'snpb')}
                        onClickButton={onClickPrevPage}
                    >{'‹'}</PageButton>
                    {' '}
                    <span className="screen-reader-text">{__('Current page', 'snpb')}</span>
                    {'top' === which ?
                        <PagingInput
                            maxPage={maxPage}
                            page={page}
                            onChangeInput={onChangeInput}
                        /> :
                        <PagingText
                            maxPage={maxPage}
                            page={page}
                        />
                    }
                    {' '}
                    <PageButton
                        active={page < maxPage}
                        className="next-page"
                        screenReaderText={__('Next page', 'snpb')}
                        onClickButton={onClickNextPage}
                    >{'›'}</PageButton>
                    {' '}
                    <PageButton
                        active={page < maxPage}
                        className="last-page"
                        screenReaderText={__('last page', 'snpb')}
                        onClickButton={onClickLastPage}
                    >{'»'}</PageButton>
                </span>
            </div>
            <br className="clear"/>
        </div>
    );
}