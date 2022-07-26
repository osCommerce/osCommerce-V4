import React from 'react';
import { connect } from 'react-redux';
import css from './Areas.scss';
import Interact from './Interact';
import Area from './Area';
import TooWindows from './ToolWindows';
import {saveSetting} from '../reducers/designer.actions';

class Areas extends React.Component {
    constructor(props) {
        super(props);

        this.saveSizesProps = {};

        this.resizableTopArea = {
            edges: { bottom : true },
            listeners: { move: (event) => { this.saveSizes('topAreaHeight', event.rect.height)}}
        };
        this.resizableLeftArea = {
            edges: { right : true },
            listeners: { move: (event) => { this.saveSizes('leftAreaWidth', event.rect.width)}}
        };
        this.resizableRightArea = {
            edges: { left : true },
            listeners: { move: (event) => { this.saveSizes('rightAreaWidth', event.rect.width)}}
        };
        this.resizableBottomArea = {
            edges: { top : true },
            listeners: { move: (event) => { this.saveSizes('bottomAreaHeight', event.rect.height)}}
        };

        this.state = {
            topAreaHeight: this.props.topAreaHeight,
            leftAreaWidth: this.props.leftAreaWidth,
            rightAreaWidth: this.props.rightAreaWidth,
            bottomAreaHeight: this.props.bottomAreaHeight,
            hide: {
                top: false,
                left: false,
                right: false,
                bottom: false,
            }
        }
    }

    saveSizes(position, size) {
        this.setState({[position]: size});

        if (!this.saveSizesProps[position]) this.saveSizesProps[position] = {};
        this.saveSizesProps[position].sizes = [position, size];

        if (!this.saveSizesProps[position].key) {
            this.saveSizesProps[position].key = true;

            setTimeout(() => {
                this.saveSizesProps[position].key = false;
                this.props.saveSettings(...this.saveSizesProps[position].sizes);
            }, 1000)
        }
    }

    getSizes(props, state){
        let response = {
            hide: {
                top: false,
                left: false,
                right: false,
                bottom: false,
            }
        };
        const positions = {
            topAreaHeight: 'top',
            leftAreaWidth: 'left',
            rightAreaWidth: 'right',
            bottomAreaHeight: 'bottom'
        };
        ['topAreaHeight', 'leftAreaWidth', 'rightAreaWidth', 'bottomAreaHeight'].map( size => {
            response[size] = state[size];
            if (
                (!props.toolsList[positions[size]] || !props.toolsList[positions[size]].length)
                && !props.toolMoves
            ) {
                response[size] = 0;
                response.hide[positions[size]] = true;
            }
        });
        return response
    }

    render() {

        const sizes = this.getSizes(this.props, this.state)

        let style = {
            gridTemplateColumns: sizes.leftAreaWidth + 'px 1fr ' + sizes.rightAreaWidth + 'px',
            gridTemplateRows: sizes.topAreaHeight + 'px calc(100vh - ' + (167 + 1*sizes.topAreaHeight + 1*sizes.bottomAreaHeight) + 'px) ' + sizes.bottomAreaHeight + 'px',
        };

        return (
            <div className="designer-areas" style={style}>
                <Interact className="designer-area-left" resizable={this.resizableLeftArea}
                          style={{display: sizes.hide.left ? 'none' : 'flex'}}>
                    <Area areaName="left"/>
                </Interact>
                <Interact className="designer-area-top" resizable={this.resizableTopArea}
                          style={{display: sizes.hide.top ? 'none' : 'flex'}}>
                    <Area areaName="top"/>
                </Interact>
                <div className="designer-area-center">
                    <Area areaName="center"/>
                </div>
                <Interact className="designer-area-bottom" resizable={this.resizableBottomArea}
                          style={{display: sizes.hide.bottom ? 'none' : 'flex'}}>
                    <Area areaName="bottom"/>
                </Interact>
                <Interact className="designer-area-right" resizable={this.resizableRightArea}
                          style={{display: sizes.hide.right ? 'none' : 'flex'}}>
                    <Area areaName="right"/>
                </Interact>
                <TooWindows/>
            </div>
        );
    }
}


const mapStateToProps = (state, ownProps) => ({
    topAreaHeight: state.designer.topAreaHeight,
    leftAreaWidth: state.designer.leftAreaWidth,
    rightAreaWidth: state.designer.rightAreaWidth,
    bottomAreaHeight: state.designer.bottomAreaHeight,
    toolsList: state.designer.toolsList,
    toolMoves: state.designer.toolMoves,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    saveSettings: (name, value) => dispatch(saveSetting(name, value)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Areas)