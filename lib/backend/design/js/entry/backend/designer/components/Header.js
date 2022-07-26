import React from 'react';
import { connect } from 'react-redux'
import Nav from 'react-bootstrap/Nav';
import NavDropdown from 'react-bootstrap/NavDropdown';
import globals from 'src/globals';
import { toggleSidebar } from '../reducers/layout.actions';

class Header extends React.Component {

    render() {
        return <div className="header">
            <a className="logo" href={globals('baseUrl')}>
                <img src={globals(['wl', 'companLogoUrl'])} alt="logoAlt" />
            </a>

            <div className={'toggle-sidebar ' + (this.props.toggleSidebar ? 'active' : '')} onClick={this.props.onToggleSidebar}></div>

            <Nav>
                <Nav.Item>
                    <Nav.Link href={globals('frontendUrl')}>{globals(['tr', 'TEXT_VIEW_SHOP'])}</Nav.Link>
                </Nav.Item>
                <Nav.Item>
                    <Nav.Link href={globals(['wl', 'servicesUrl'])}>{globals(['wl', 'servicesText'])}</Nav.Link>
                </Nav.Item>
                <Nav.Item>
                    <Nav.Link href={globals(['wl', 'supportUrl'])}>{globals(['wl', 'supportText'])}</Nav.Link>
                </Nav.Item>
                <Nav.Item>
                    <Nav.Link href={globals(['wl', 'contactUsUrl'])}>{globals(['wl', 'contactUsText'])}</Nav.Link>
                </Nav.Item>

                <NavDropdown title={globals(['tr', 'TEXT_MY_ACCOUNT'], 'My Account')} id="nav-dropdown">
                    <NavDropdown.Item eventKey="4.1">
                        <div className="admin-view">
                            {globals(['adminData', 'avatar']) ?
                                <img src={globals('DIR_WS_CATALOG_IMAGES') + globals(['adminData', 'avatar'])}/>
                                :
                                <i className="icon-user"></i>
                            }
                            <span className="full-name">{globals(['adminData', 'firstname'])}  {globals(['adminData', 'lastname'])}</span>
                        </div>
                    </NavDropdown.Item>
                    <NavDropdown.Item href={globals('baseUrl') + 'adminaccount'}>{globals(['tr', 'TEXT_MY_ACCOUNT'])}</NavDropdown.Item>
                    <NavDropdown.Item href={globals('baseUrl') + 'logout'}>{globals(['tr', 'TEXT_HEADER_LOGOUT'])}</NavDropdown.Item>
                </NavDropdown>
            </Nav>

        </div>;
    }
}

const mapStateToProps = (state, ownProps) => ({
    toggleSidebar: state.layout.toggleSidebar
})

const mapDispatchToProps = (dispatch, ownProps) => ({
    onToggleSidebar: () => dispatch(toggleSidebar())
})

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Header)