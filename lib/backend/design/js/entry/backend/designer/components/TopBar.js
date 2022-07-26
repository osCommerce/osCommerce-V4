import React from 'react';
import { connect } from 'react-redux';
import globals from 'src/globals';
import Clock from './Clock';
//import CreateThemeButton from './themes/CreateThemeButton';

class TopBar extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div className="top-bar">
                <div className="page-title">{this.props.pageTitle}</div>
                <div className="buttons">
                    {/*<CreateThemeButton></CreateThemeButton>*/}
                </div>
                <Clock />
            </div>
        );
    }
}


const mapStateToProps = (state, ownProps) => ({
    pageTitle: state.layout.pageTitle,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    /*onResize: sidebarWidth => dispatch({
        type: 'RESIZE_SIDEBAR',
        sidebarWidth
    }),
    onToggleMenuType: (menuType) => dispatch({
        type: 'TOGGLE_MENU_TYPE',
        menuType
    })*/
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(TopBar)