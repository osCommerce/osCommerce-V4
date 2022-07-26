import React from 'react';
import PopUpHeader from './Header';
import PopUpContent from './Content';
import PopUpFooter from './Footer';
import Interact from '../Interact';
import { connect } from 'react-redux';
import { saveSetting } from '../../reducers/designer.actions';

class PopUp extends React.Component {

    static Header = PopUpHeader;
    static Content = PopUpContent;
    static Footer = PopUpFooter;

    constructor(props) {
        super(props);

        this.saveSizesProps = {};

        this.resizable = {
            edges: { left: true, right: true, bottom: true, top: true },
            margin: 5
        };
        this.draggable = {
            allowFrom: '.popup-heading',
        };

        let width = props.popUpSettings.width || 500;
        let height = props.popUpSettings.height || 500;

        if (width > window.innerWidth - 20) width = window.innerWidth - 20;
        if (height > window.innerHeight - 20) height = window.innerHeight - 20;

        let dataX = props.popUpSettings.dataX || ((window.innerWidth - width) / 2);
        let dataY = props.popUpSettings.dataY || ((window.innerHeight - height) / 2);

        if (dataX < 0) dataX = 10;
        if (dataY < 0) dataY = 10;
        if (dataX > window.innerWidth - width - 10) dataX = window.innerWidth - width - 10;
        if (dataY > window.innerHeight - height - 10) dataY = window.innerHeight - height - 10;

        this.state = {
            dataX: dataX,
            dataY: dataY,
            width: width,
            height: height
        }
    }

    onChangeSize(sizes, name) {
        if (!name || !sizes) return;

        if (!this.saveSizesProps) this.saveSizesProps = {};
        this.saveSizesProps.sizes = {
            dataX: sizes.dataX,
            dataY: sizes.dataY,
            width: sizes.width,
            height: sizes.height
        };

        if (!this.saveSizesProps.key) {
            this.saveSizesProps.key = true;

            setTimeout(() => {
                this.saveSizesProps.key = false;
                this.props.saveSettings({[name]: this.saveSizesProps.sizes});
            }, 1000)
        }
    }

    render() {
        return (
            <div className="popup-box-wrap">
                {this.props.shadow ? <div className="around-pop-up"></div> : ''}
                <Interact
                    className="popup-box"
                    resizable={this.resizable}
                    draggable={this.draggable}
                    onChange={sizes => this.onChangeSize(sizes, this.props.name)}
                    width={this.state.width}
                    height={this.state.height}
                    dataX={this.state.dataX}
                    dataY={this.state.dataY}
                >
                    <div className="close" onClick={() => this.props.close()}>&times;</div>
                    {this.props.children}
                </Interact>
            </div>
        )
    }
}

const mapStateToProps = (state, ownProps) => ({
    popUpSettings: state.designer.popUpSettings && state.designer.popUpSettings[ownProps.name] ? state.designer.popUpSettings[ownProps.name] : {},
    shadow: ownProps.shadow,
    children: ownProps.children,
    name: ownProps.name,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    saveSettings: (value) => dispatch(saveSetting('popUpSettings', value)),
    close: ownProps.close
})

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(PopUp)