import React from 'react';

export default function SubSubSUb(props) {
    const {
        stats,
        current,
        onClickSub
    } = props;

    return (
        <ul className="subsubsub">
            {stats.map((type, idx) => {
                if (type.count || 'all' === type.type) {
                    return (
                        <li key={type.type} className={"api-" + type.type}>
                            <a
                                className={type.type === current ? 'current' : ''}
                                href="#"
                                onClick={(e) => {
                                    e.preventDefault();
                                    onClickSub(type.type);
                                }}
                            >
                                {type.label + ' '}
                                <span className="count">({type.count})</span>
                            </a>
                            {(stats.length > 1 && idx + 1 < stats.length) ? '| ' : ''}
                        </li>
                    );
                }
            })}
        </ul>
    )
}
