import React, {useEffect, useState} from 'react';
import ReactDOM from "react-dom";
import {__} from '@wordpress/i18n';

import SubSubSUb from "./wc-status-api-log/sub-sub-sub";
import TableNav from "./wc-status-api-log/table-nav";
import ListTable from "./wc-status-api-log/list-table";

import './wc-status-api-log.css';

function NicePayApiLog() {
    const [page, setPage] = useState(1);
    const [perPage, setPerPage] = useState(25);
    const [stats, setStats] = useState([]);
    const [current, setCurrent] = useState('all');
    const [records, setRecords] = useState([]);
    const [foundRows, setFoundRows] = useState(0);
    const [maxPage, setMaxPage] = useState(0);

    const updateState = function (data) {
        setStats(data.stats);
        setRecords(data.log.records);
        setFoundRows(data.log.found_rows);
        setMaxPage(data.log.max_page);
    };

    const fetchData = function (params) {
        params.action = 'snpb_get_api_log';

        const url = window.ajaxurl + '?' +
            Object.entries(params).map(([k, v]) => k + '=' + encodeURIComponent(v)).join('&');

        fetch(url)
            .then(r => r.json())
            .then(r => {
                if (r.success) {
                    updateState(r.data)
                }
            });
    };

    const firstPage = function (e) {
        e.preventDefault();
        setPage(1);
        fetchData({
            page: 1,
            per_page: perPage,
        });
    };

    const prevPage = function (e) {
        e.preventDefault();
        if (page > 1) {
            setPage(page - 1);
            fetchData({
                page: page - 1,
                per_page: perPage,
            });
        }
    };

    const nextPage = function (e) {
        e.preventDefault();
        if (page < maxPage) {
            setPage(page + 1);
            fetchData({
                page: page + 1,
                per_page: perPage,
            });
        }
    };

    const lastPage = function (e) {
        e.preventDefault();
        if (page < maxPage) {
            setPage(maxPage);
            fetchData({
                page: maxPage,
                per_page: perPage,
            });
        }
    };

    const onChangeInput = function (e) {
        const value = parseInt(e.target.value);
        console.log(value);
        if (0 < value && value <= maxPage) {
            setPage(value);
            fetchData({
                page: value,
                per_page: perPage,
            });
        }
    }

    const onClickSub = function (type) {
        setCurrent(type);
        setPage(1);
        fetchData({
            api: type,
            page: 1,
            per_page: perPage,
        });
    }

    useEffect(() => {
        fetchData({
            page: page,
            per_page: perPage,
        });
    }, []);

    return (
        <>
            <h2 className="screen-reader-text">
                {__('Filter logs', 'snpb')}
            </h2>

            <SubSubSUb
                stats={stats}
                current={current}
                onClickSub={onClickSub}
            />

            <div id="api-log-filter">
                <TableNav
                    which="top"
                    page={page}
                    maxPage={maxPage}
                    foundRows={foundRows}
                    onClickFirstPage={firstPage}
                    onClickPrevPage={prevPage}
                    onClickNextPage={nextPage}
                    onClickLastPage={lastPage}
                    onChangeInput={onChangeInput}
                />

                <h2 className="screen-reader-text">
                    {__('Logs list', 'snpb')}
                </h2>

                <ListTable records={records}/>

                <TableNav
                    which="bottom"
                    page={page}
                    maxPage={maxPage}
                    foundRows={foundRows}
                    onClickFirstPage={firstPage}
                    onClickPrevPage={prevPage}
                    onClickNextPage={nextPage}
                    onClickLastPage={lastPage}
                />
            </div>

            <div className="clear"/>
        </>
    );
}

document.addEventListener('DOMContentLoaded', () => {
    ReactDOM.render(<NicePayApiLog/>, document.getElementById('snpb-api-log'));
});
