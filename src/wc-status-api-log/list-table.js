import React from 'react';
import {__} from '@wordpress/i18n';

function LabelsRow() {
    return (
        <tr>
            <th
                id="id"
                scope="col"
                className="manage-column column-id"
            >
                {__('ID', 'snpb')}
            </th>
            <th
                id="user"
                scope="col"
                className="manage-column column-user"
            >
                {__('User', 'snpb')}
            </th>
            <th
                id="datetime"
                scope="col"
                className="manage-column column-datetime"
            >
                {__('Datetime', 'snpb')}
            </th>
            <th
                id="ip"
                scope="col"
                className="manage-column column-ip"
            >
                {__('IP', 'snpb')}
            </th>
            <th
                id="api"
                scope="col"
                className="manage-column column-api"
            >
                {__('API', 'snpb')}
            </th>
            <th
                id="result_code"
                scope="col"
                className="manage-column column-result_code"
            >
                {__('Result Code', 'snpb')}
            </th>
            <th
                id="response"
                scope="col"
                className="manage-column column-response"
            >
                {__('Response', 'snpb')}
            </th>
        </tr>
    );
}

function ColumnUserData(props) {
    const userData = props.userData;

    if (userData) {
        return (
            <>
                {'[#' + userData.id + '] '}
                <a href={userData.edit_link} target="_blank">{userData.display_name}</a>
            </>
        );
    } else {
        return '';
    }
}

function TableBody(props) {
    if (props.records.length) {
        return (
            <>
                {props.records.map(record => (
                    <tr key={record.id}>
                        <th scope="row"
                            className="id column-id">
                            {record.id}
                        </th>
                        <td className="user column-user">
                            <ColumnUserData userData={record.user_data}/>
                        </td>
                        <td className="datetime column-datetime">
                            {record.datetime}
                        </td>
                        <td className="ip column-ip">
                            {record.ip}
                        </td>
                        <td className="api column-api">
                            {record.api}
                        </td>
                        <td className="result-code column-result-code">
                            {record.result_code}
                        </td>
                        <td className="response column-response">
                            {record.response}
                        </td>
                    </tr>
                ))}
            </>
        )
    } else {
        return (
            <tr className="no-items">
                <td className="colspanchange" colSpan="7">No items</td>
            </tr>
        )
    }
}

export default function ListTable(props) {
    return (
        <table className="wp-list-table widefat fixed striped table-view-list options">
            <thead><LabelsRow/></thead>
            <tbody id="the-list"><TableBody {...props}/></tbody>
            <tfoot><LabelsRow/></tfoot>
        </table>
    );
}
