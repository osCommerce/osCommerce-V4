import React from 'react';
import globals from 'src/globals';

export default class Footer extends React.Component {
    render() {
        const date = new Date();

        return (
            <div className="footer">
                {globals(['wl', 'wlFooter']) ? (
                    <ul>
                        <li>
                            Copyright &copy;  {date.getYear()} <a target="_blank" href="http://loadedcommerce.com">{globals(['wl', 'WL_COMPANY_NAME'])}</a>. All rights reserved.
                        </li>
                    </ul>
                ) : (
                    <ul>
                        <li>
                            <span dangerouslySetInnerHTML={{__html: globals(['tr', 'TEXT_COPYRIGHT'])}} />
                            <span> {date.getFullYear()} </span>
                            <a target="_blank" href="http://www.holbi.co.uk">{globals(['tr', 'TEXT_COPYRIGHT_HOLBI'])}</a>
                        </li>
                        <li>
                            <span dangerouslySetInnerHTML={{__html: globals(['tr', 'TEXT_FOOTER_BOTTOM'])}} />
                        </li>
                        <li>
                            <span dangerouslySetInnerHTML={{__html: globals(['tr', 'TEXT_FOOTER_COPYRIGHT'])}} />
                            <span> {date.getFullYear()} </span>
                            <span dangerouslySetInnerHTML={{__html: globals(['tr', 'TEXT_COPYRIGHT_HOLBI'])}} />
                        </li>
                    </ul>
                )}
            </div>
        );
    }
}