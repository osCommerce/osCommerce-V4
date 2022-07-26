import React from 'react';
import { connect } from 'react-redux';
import Interact from './Interact';
import NavigationTree from './NavigationTree';
import { Scrollbars } from 'react-custom-scrollbars';
import globals from 'src/globals';
import { resizeSidebar, toggleMenuType } from '../reducers/layout.actions';

class Navigation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            width: 200,
            searchValue: ''
        };

        this.resizableOptions = {
            edges: {
                right: '.right-resize-handler',
            },
            onResize: (event) => {
                this.props.onResize(event.rect.width)
            }
        };

        this.searchChange = this.searchChange.bind(this);
    }

    searchChange(event) {
        this.setState({searchValue: event.target.value});
    }

    render() {
        return (
            <Interact className="navigation" style={{width: this.props.sidebarWidth, display: this.props.toggleSidebar ? 'block' : 'none'}} resizable={this.resizableOptions}>
                <div className="right-resize-handler" />
                <Scrollbars style={{width: this.props.sidebarWidth-5, height: '100%'}} autoHide>
                    <div className="sidebar-search">
                        <div className="input-box">
                        <span className="search">
                            <i className="icon-search"></i>
                        </span>
                            <span>
                            <input
                                type="text"
                                placeholder="Search..."
                                autoComplete="off"
                                onKeyUp={this.searchChange}/>
                        </span>
                        </div>
                    </div>

                    <ul className="tabNavigation" id="menuSwitcher">
                        <li onClick={() => this.props.onToggleMenuType('basic')}>
                            <span className={'advanced' + (this.props.menuType === 'basic' ? ' active' : '')}>{globals(['tr', 'TEXT_EVERYDAY_ACTIVITIES'])}</span>
                        </li>
                        <li onClick={() => this.props.onToggleMenuType('advanced')}>
                            <span className={'advanced' + (!this.props.menuType || this.props.menuType === 'advanced' ? ' active' : '')}>{globals(['tr', 'TEXT_FULL_MENU'])}</span>
                        </li>
                    </ul>

                    <NavigationTree search={this.state.searchValue} treeData={globals('mainMenu')} type={this.props.menuType}/>
                </Scrollbars>
            </Interact>
        );
    }
}


const mapStateToProps = (state, ownProps) => ({
    toggleSidebar: state.layout.toggleSidebar,
    sidebarWidth: state.layout.sidebarWidth,
    menuType: state.layout.menuType
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    onResize: sidebarWidth => dispatch(resizeSidebar(sidebarWidth)),
    onToggleMenuType: (menuType) => dispatch(toggleMenuType(menuType))
})

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Navigation)