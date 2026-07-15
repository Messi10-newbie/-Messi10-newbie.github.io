public class StandardSeat extends Seat {

    private static final double PRICE = 20.0;

    public StandardSeat(String seatId) {
        super(seatId);
    }

    @Override
    public double getPrice() { return PRICE; }

    @Override
    public String getType() { return "Standard"; }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof StandardSeat)) return false;
        return super.equals(obj);
    }
}
