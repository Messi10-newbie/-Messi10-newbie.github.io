public class VIPSeat extends Seat {

    private static final double PRICE = 35.0;

    public VIPSeat(String seatId) {
        super(seatId);
    }

    @Override
    public double getPrice() { return PRICE; }

    @Override
    public String getType() { return "VIP"; }

    @Override
    public int hashCode() { return super.hashCode(); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof VIPSeat)) return false;
        return super.equals(obj);
    }
}
